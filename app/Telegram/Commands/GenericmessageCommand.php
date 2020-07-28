<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Chat;

use App\FlightTracking;
use App\Http\Controllers\SendMessage;
use App\MenuItem;
use DateInterval;

use App\Telegram\Helpers\ConvertDate;
use App\Telegram\Helpers\GetApi;
use App\Telegram\keyboards\CreateInlineKeyboard;
use App\Telegram\keyboards\LangInlineKeyboard;


use DateTime;
use DateTimeZone;
use App\Telegram\Helpers;
use Illuminate\Support\Facades\Lang;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use App\Telegram\keyboards\GetMenuButtonMessage;


use App\Conversation as Convers;

/**
 * Generic message command
 *
 * Gets executed when any type of message is sent.
 */
class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';
    /**
     * @var string
     */
    protected $description = 'Handle generic message';
    /**
     * @var string
     */
    protected $version = '1.1.0';
    /**
     * @var bool
     */
    protected $need_mysql = true;
    public $chat_id;
    public $text;

    /**
     * Command execute method if MySQL is required but not available
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function executeNoDb()
    {
        // Do nothing
        return Request::emptyResponse();
    }

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute()
    {


        //If a conversation is busy, execute the conversation command after handling the message


        $message = $this->getMessage();
        $chat_id = $message->getChat()->id;
        $text = $message->getText();

        $this->chat_id = $chat_id;
        $this->text = $text;


        $chat = Chat::where('id', $chat_id)->first();

        /**
         * Начало работы с Меню
         * Достаем Названия кнопок и смотрим совпадает ли присланное сообщение с названием кнопки
         *
         */
        $limit = setting('telegram.count_of_main_menu');
        $default_limit = 9;
        if (!$limit) {
            $limit = $default_limit;
        }
        $main_menu_items = MenuItem::
        where('menu_id', '2')
            ->orderBy('order', 'ASC')
            ->limit($limit)->get();
        $main_menu_items = $main_menu_items->translate($chat->lang);
        /**
         * BACK BUTTON
         */
        $prefix = Helpers\FlightHelper::telegram_config('buttons.pref_back_menu', $chat->lang);
        if (!$prefix) {
            $prefix = '';
        } else {
            $prefix = $prefix . ' ';
        }
        $postfix = Helpers\FlightHelper::telegram_config('buttons.post_back_menu', $chat->lang);;
        if (!$postfix) {
            $postfix = '';
        } else {
            $postfix = ' ' . $postfix;
        }
        foreach ($main_menu_items as $menu_item) {
            if ($text == $prefix . $menu_item->title . $postfix) {
                $this->closeConvers($message, $chat_id);

                $subMenu = new GetMenuButtonMessage($menu_item->parent_id);
                $keyboard = $subMenu->get_parentmenu($chat_id);


                $data = [
                    'chat_id' => $chat_id,
                    'text' => $text,
                    'reply_markup' => $keyboard,
                ];

                return Request::sendMessage($data);
            }
        }
        /**
         * Конец работы с Меню
         */
        /**
         * НАЖАТИЕ КНОПКИ МОЙ СПИСОК РЕЙСОВ
         */
        if ($text == $this->getTitle('buttons.myFlightList')) {
            $usersTracksFlights = FlightTracking::where("status", 1)->where('chat_id', $chat_id)->where('person_id', $message->getFrom()->getId())->get();
            $this->closeConvers($message, $chat_id);

            if ($usersTracksFlights->isEmpty()) {
                $data = [
                    "chat_id" => $chat_id,
                    "text" => Lang::get("messages.emptyFlightsList", [], "$chat->lang"),
                ];
            } else {
                $keyboard = new CreateInlineKeyboard($chat_id);
                $keyboard = $keyboard->createMyFlightsList($usersTracksFlights);
                $data = [
                    "chat_id" => $chat_id,
                    "text" => Lang::get("messages.FlightsListText", [], "$chat->lang"),
                    "reply_markup" => $keyboard
                ];
            }


//          $data=['chat_id'=>$chat_id];
//          $data["text"]="YOUR LIST";
//          $data["reply_markup"]=$keyboard;
            Request::sendMessage($data);
        }

        /**
         * КНОПКА СМЕНЫ языка
         *
         *
         */
        $t = $this->getTitle('buttons.change_lang');
        if ($text == $t) {
            $this->closeConvers($message, $chat_id);
            $lang_menu = new LangInlineKeyboard($chat_id);
            $lang_menu->create_inline_menu();
        }
//        $t=$this->getTitle('buttons.settings');
//        if($text==$t){
//            $lang_menu= new LangInlineKeyboard($chat_id);
//            $lang_menu->create_inline_menu();
//        }
        /**
         * НАЖАТА КНОПКА ВАША ДАТА||СЕГОДНЯ||ЗАВТРА||ВЧЕРА
         *
         */

        $notes = [];
        $data['chat_id'] = $this->chat_id;
        $format = "Y-m-d";
        $date = false;
        if ($text == $this->getTitle('buttons.today')) {
            $this->closeConvers($message, $chat_id);
            $date = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
//            dd($date);
        } elseif ($text == $this->getTitle('buttons.tomorrow')) {
            $this->closeConvers($message, $chat_id);
            $date = new DateTime('tomorrow', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);

//            dd($date);
        } elseif ($text == $this->getTitle('buttons.yesterday')) {
            $this->closeConvers($message, $chat_id);
//            $d = strtotime("yesterday");
            $date = new DateTime('yesterday', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
//            $date = date($format, $d);
//            dd($date);
        } elseif ($text == $this->getTitle('buttons.your_date')) {
            $this->closeConvers($message, $chat_id);
            $convers = new Conversation($message->getFrom()->getId(), $chat_id, "your_date");

            $notes = &$convers->notes;
            !is_array($notes) && $notes = [];

            //cache data from the tracking session if any
            $state = 0;
            if (isset($notes['state'])) {
                $state = $notes['state'];
            }

            $result = Request::emptyResponse();

            $notes['state'] = 0;
            $convers->update();
            $currentTime1 = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));
            $twoDay = $currentTime1->add(new DateInterval("P2D"));
            $data['text'] = Lang::get("messages.inputYourDate", [], "$chat->lang") . "\n" .
                Lang::get("messages.example", ["date"=>$twoDay->format("d.m")], "$chat->lang");


            Request::sendMessage($data);
            $notes['state'] = 1;

            $convers->update();
        } else {
            foreach ($main_menu_items as $menu_item) {
                if ($text == $menu_item->title) {
                    $this->closeConvers($message, $chat_id);
                    $subMenu = new GetMenuButtonMessage($menu_item->id);
                    $keyboard = $subMenu->get_submenu($chat_id);
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => $text,
                        'reply_markup' => $keyboard,
                    ];
                    return Request::sendMessage($data);
                }
            }
            $conversModel = Convers::where('user_id', $message->getFrom()->getId())
                ->where('chat_id', $chat_id)
                ->where('command', "your_date")
                ->where('status', 'active')->first();
            if ($conversModel) {


                $newconversModel = Convers::find($conversModel->id);
                $notes = \GuzzleHttp\json_decode($newconversModel->notes);
                if ($notes->state == 1) {

                    $notes->date = $text;
                    $notes->state++;
                    $data['text'] = $notes->date;

                    $newconversModel->notes = \GuzzleHttp\json_encode($notes);
                    $date_array = explode('.', $notes->date);

                    $errorMessage = "";
                    $year = date("Y");

                    if (count($date_array) !== 2) {
                        $errorMessage = Lang::get("messages.wrongDateInput", [], "$chat->lang");

                    } else {
                        if (!is_numeric($date_array[0]) && !is_numeric($date_array[1])) {
                            $errorMessage = Lang::get("messages.wrongDateInput", [], "$chat->lang");
                        }
                    }

                    if (!$errorMessage) {
                        if ($date_array[1] < date("m")) {
//                     $errorMessage="TRANSLATE OUTDATED";
                            $year++;
                        } elseif ($date_array[1] == date("m")) {
                            if ($date_array[0] < date("d") - 1) {
//                         $errorMessage="TRANSLATE OUTDATED";
                                $year++;
                            }
                        }
                        $date = "$year-$date_array[1]-$date_array[0]";

                        // dd($conversModel);

                    } else {
                        $data["text"] = $errorMessage;
                        return Request::sendMessage($data);
                    }
                    $newconversModel->save();

                }
            }
        }
        if ($date) {
            $getApi=new GetApi();
            $api = $getApi->getFlightsByDate($date);
//            dd($date);
//            dd($api);

            if (isset($api->code)) {
                if ($api->code == 404) {

                    $data["text"] = $errorMessage = Lang::get("messages.NoFlightsOnThisDate", [], "$chat->lang");
                    return Request::sendMessage($data);
                }
                if ($api->code == 500) {

                    $data["text"] = $errorMessage = Lang::get("messages.serverError", [], "$chat->lang");
                    return Request::sendMessage($data);
                }
            }
            if ($api) {
                $keyboard = new CreateInlineKeyboard($chat_id);
                $keyboard = $keyboard->createFlightsList($api);
                $data["text"] = ConvertDate::ConvertToWordMonth($date, $chat->lang) . "\n" . Lang::get("messages.list", [], "$chat->lang") . " 1 " . Lang::get("messages.of", [], "$chat->lang") ." ". $getApi->getLastPage();
                $data["reply_markup"] = $keyboard;
                return Request::sendMessage($data);
            } else {
                $data["text"] = $errorMessage = Lang::get("messages.wrongDateInput", [], "$chat->lang");

                return Request::sendMessage($data);
            }
        }
        /**
         *
         *
         * кнопка рассылки
         */




        /**
         * Конец Работы с главными кнопками
         *
         *
         */


        /**
         *
         *
         *
         * MAIN MENU GENERATION
         */



        /**
         *
         * Работа с геолокацией
         */

//        $test=$message->getLocation()->toJson();
//        $test=json_decode($test);
//        $lat= $test->latitude;
//        $long=$test->longitude;
//        $key=env('GOOGLE_MAPS_API_KEY');
//        $lang=$chat->lang;
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&key=$key&language=$lang",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "GET",
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//
//        $someObject = json_decode($response);
//        var_dump($someObject);      // Dump all data of the Object
//
//        $answer=$someObject->results[0]->formatted_address;
//        $data = [
//            'chat_id' => $chat_id,
//            'text'    => $answer,
//
//        ];
//        Request::sendMessage($data);


    }

    public function closeConvers($message, $chat_id)
    {
        Convers::where('user_id', $message->getFrom()->getId())
            ->where('chat_id', $chat_id)
            ->delete();

    }

    public function getTitle($button)
    {
        $item = MenuItem::where('id', telegram_config_no_translate($button))->
        first();
        $chat = Chat::where('id', $this->chat_id)->first();
        $item = $item->translate($chat->lang);

        return $item['title'];
    }
}
