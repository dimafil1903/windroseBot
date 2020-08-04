<?php


namespace App\Viber\Commands;


use App\FlightTracking;
use App\MenuItem;

use App\Telegram\Helpers\ConvertDate;
use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;

use App\Viber\Keyboards\FlightKeyboard;
use App\Viber\Keyboards\FlightsKeyboard;
use App\Viber\Keyboards\LanguageKeyboard;
use App\Viber\Keyboards\MainMenu;
use App\Viber\Keyboards\MyListKeyboard;
use App\Viber\ViberBot;
use App\ViberConversation;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

use Paragraf\ViberBot\Bot;
use Paragraf\ViberBot\Event\MessageEvent;
use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;
use Paragraf\ViberBot\Model\ViberUser;
use Paragraf\ViberBot\TextMessage;

class MessageCommand extends ViberBot
{

    public $chat;
    public $conversation;

    public function execute()
    {


        $message = $this->request->message;
        $user = new ViberUser($this->getSender()['id'], $this->getSender()['name']);
        Log::alert(\GuzzleHttp\json_encode(file_get_contents('php://input')));
        $this->conversation = ViberConversation::where("user_id", $user->getId())
            ->where("command", "message")
            ->where("status", "active")
            ->first();
        if (isset($message["text"])) {
            $text = $message["text"];
        }
        $answer = null;
        $keyboard = null;

        $messagePiece = explode("_", $message["text"]);
        $this->chat = \App\ViberUser::where("user_id", $user->getId())->first();
        if ($messagePiece[0] == "lang") {

            $this->chat = \App\ViberUser::find($this->chat->id);
            $this->chat->lang = $messagePiece[1];
            $this->chat->save();
            $keyboard = new MainMenu($user->getId());
            $keyboard = $keyboard->getKeyboard($this->chat->lang);
            $answer = Lang::get("messages.changedLang", [], $this->chat->lang);
        }

        $main_menu_items = MenuItem::
        where('menu_id', '2')
            ->orderBy('order', 'ASC')
//            ->whereNull("parent_id")
            ->get();
        $main_menu_items = $main_menu_items->translate($this->chat->lang);

        $menu_item = $main_menu_items->where('title', $text)->first();


        $keyboardMessage = new KeyboardMessage();
        if ($menu_item) {
            $this->closeConvers($user->getId());
            $subMenu = new MainMenu($user->getId());


            $keyboard = $subMenu->getSubMenu($menu_item->id, $this->chat->lang);

            $keyboard = $keyboardMessage->setKeyboard($keyboard);
            $answer = "$menu_item->title";
        }


        /**
         * Language KEYBOARD
         */

        if (!$this->chat->lang) {
            $keyboard = new LanguageKeyboard();
            $keyboard = $keyboard->getKeyboard();
            $answer = "Оберіть мову/Choose language";
        }
        if ($text == $this->getTitle('buttons.change_lang')) {
            $keyboard = new LanguageKeyboard();
            $keyboard = $keyboard->getKeyboard();
            $answer = "Оберіть мову/Choose language";
        }

        /**
         * create main menu keyboard event
         */
        foreach ($main_menu_items as $item) {

            $textExit = new MainMenu($user->getId());
            if ($text == $textExit->exit_button($item, $this->chat->lang) || $text == "BackToSchedule") {

                $this->closeConvers($user->getId());
                $keyboard = new MainMenu($user->getId());
                $keyboard = $keyboard->getKeyboard($this->chat->lang);
                $answer = "Its main menu";
            }
            if ($text == "BackToSchedule") {
                $answer = Lang::get('messages.returnToMainMenu',[],$this->chat->lang);
            }
            if ($text == $textExit->exit_button($item, $this->chat->lang)) {
                $answer = Lang::get('messages.returnToMainMenu',[],$this->chat->lang);
            }

        }
        /**
         * date format
         */
        $format = "Y-m-d";
        /**
         * default value of date
         */
        $date = false;
        $yourDate = false;
        /**
         * default number of page
         */


        $page = 1;
        $fieldStatus = "hidden";
        if ($text == $this->getTitle('buttons.today')) {
            $this->closeConvers($user->getId());
            $date = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
            $answer = Lang::get('messages.FlightListsOn',['date'=>ConvertDate::ConvertToWordMonth($date,$this->chat->lang)],$this->chat->lang);


//            dd($date);
        } elseif ($text == $this->getTitle('buttons.tomorrow')) {
            $this->closeConvers($user->getId());
            $date = new DateTime('tomorrow', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
            $answer = Lang::get('messages.FlightListsOn',['date'=>ConvertDate::ConvertToWordMonth($date,$this->chat->lang)],$this->chat->lang);


        } elseif ($text == $this->getTitle('buttons.yesterday')) {
            $this->closeConvers($user->getId());

            $date = new DateTime('yesterday', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
            $answer = Lang::get('messages.FlightListsOn',['date'=>ConvertDate::ConvertToWordMonth($date,$this->chat->lang)],$this->chat->lang);


        } elseif ($text == $this->getTitle('buttons.your_date')) {
            $this->closeConvers($user->getId());
            $subMenu = new MainMenu($user->getId());
            $currentTime1 = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));
            $twoDay = $currentTime1->add(new DateInterval("P2D"));
            $answer = Lang::get("messages.inputYourDate", [], $this->chat->lang) . "\n" .
                Lang::get("messages.example", ["date" => $twoDay->format("d.m")], $this->chat->lang);

            if (!$this->conversation) {
                $conversation = ViberConversation::updateOrCreate(
                    ['user_id' => $user->getId(),
                        "chat_id" => $user->getId(),
                        "status" => "active",
                        "command" => "message"], [
                        "notes" => \GuzzleHttp\json_encode(["state" => 0])
                    ]
                );
                $this->conversation = ViberConversation::find($conversation->id);
            }


            $keyboard = $subMenu->getSubMenu($menu_item->id, $this->chat->lang, "regular");

            $keyboard = $keyboardMessage->setKeyboard($keyboard);
        } elseif ($messagePiece[0] == "page" || $messagePiece[0] == "backToFlightList") {
            $this->closeConvers($user->getId());
            $date = $messagePiece[1];
            Log::warning($date);
            if (isset($messagePiece[2])) {
                $page = $messagePiece[2];
            }
            $fieldStatus = $messagePiece[3];

            if ($messagePiece[0] == "page") {

                $answer = Lang::get('messages.FlightListsOn',['date'=>ConvertDate::ConvertToWordMonth($date,$this->chat->lang)],$this->chat->lang).
                   "\n". Lang::get('messages.page',['page'=>$page],$this->chat->lang) ;

                if ($page < 1) {
                    $answer = Lang::get("messages.YouAlreadyAtStart", [], $this->chat->lang);
                    $page = 1;
                } elseif ($page > $messagePiece[4]) {
                    $answer = Lang::get("messages.listIsOver", [], $this->chat->lang);
                    $page = $messagePiece[4];
                }
            }
        }
        /**
         * Диалог Для получения даты от пользователя
         */
        $this->conversation = ViberConversation::where("user_id", $user->getId())
            ->where("command", "message")
            ->where("status", "active")
            ->first();
        if ($this->conversation) {
            $notes = (array)\GuzzleHttp\json_decode($this->conversation->notes);
//            !is_array($notes) && $notes = [];

            //cache data from the tracking session if any
            $state = 0;
            if (isset($notes['state'])) {
                $state = $notes['state'];
            }
            $notes['state'] = 1;
            $this->conversation->notes = \GuzzleHttp\json_encode($notes);
            $this->conversation->save();
            if ($state == 1) {
                $errorMessage = "";
                $year = date("Y");
                $date_array = explode('.', $text);
                $dt = DateTime::createFromFormat("d.m", $text);
                $dtf = DateTime::createFromFormat("d.m", $text);
//            Log::info(\GuzzleHttp\json_encode($dtf->format("d.m")));
                if (!($dt !== false && !array_sum($dt::getLastErrors()))) {
                    $errorMessage = Lang::get("messages.wrongDateInput", [], $this->chat->lang);
                }

                if (!$errorMessage) {
                    if ($dtf->format("m") < date("m")) {

                        $dt->add(new DateInterval("P1Y"));
                    } elseif ($dtf->format("m") == date("m")) {
                        if ($dtf->format("d") < date("d") - 1) {

                            $dt->add(new DateInterval("P1Y"));
                        }
                    }
                    $state++;
                    $notes['state'] = $state;
                    $date = $dt->format("Y-m-d");
                    $this->conversation->notes = \GuzzleHttp\json_encode($notes);
                    $this->conversation->save();
                    $answer = Lang::get('messages.FlightListsOn',['date'=>ConvertDate::ConvertToWordMonth($date,$this->chat->lang)],$this->chat->lang);
                    $yourDate = true;
                } else {
                    if ($messagePiece[0] == "page" || $messagePiece[0] == "BackToSchedule" || $messagePiece[0] == "flight" || $messagePiece == "backToFlightList") {
                        $answer = "";
                    } else {
                        $answer = $errorMessage;

                        $textExit = new MainMenu($user->getId());

                        $item = MenuItem::where('id', telegram_config_no_translate("buttons.your_date"))->
                        first();
//                        $exitTitle = $textExit->exit_button($item, $this->chat->lang);
                        $keyboard = new Keyboard([(new Button('reply', "BackToSchedule", "<font color='#FFFFFF'>" . Lang::get('messages.cancel', [], $this->chat->lang) . "</font>"))->setBgColor("#8176d6")]);
                        $keyboard = $keyboardMessage->setKeyboard($keyboard);
                    }

                }
            }


        }

        if ($messagePiece[0] == "backToFlightList") {
            $date = $messagePiece[1];
            $page = $messagePiece[2];
            $fieldStatus = $messagePiece[3];
            $answer = Lang::get('messages.returnToList',[],$this->chat->lang);
        }
        if ($date) {
            $getApi = new GetApi();
            $api = $getApi->getFlightsByDate($date);
//            dd($date);
//            dd($api);

            if (isset($api->code)) {
                if ($api->code == 404) {

                    $answer = Lang::get("messages.NoFlightsOnThisDate", [], $this->chat->lang);

                }
                if ($api->code == 500) {

                    $answer = Lang::get("messages.serverError", [], $this->chat->lang);

                }
            }
            if ($api) {
                $flightKeyboard = new FlightsKeyboard();
                $keyboard = $flightKeyboard->flights($api, $user->getId(), $page, $fieldStatus);
                if ($yourDate) {
                    $keyboard = $flightKeyboard->flights($api, $user->getId(), $page, "hidden");
                }


            }else{
                $answer = Lang::get("messages.NoFlightsOnThisDate", [], $this->chat->lang);
                $keyboard = new Keyboard([(new Button('reply', "BackToSchedule", "<font color='#FFFFFF'>" . Lang::get('messages.cancel', [], $this->chat->lang) . "</font>"))->setBgColor("#8176d6")]);
                $keyboard = $keyboardMessage->setKeyboard($keyboard);
            }

        }


        /**
         * Показываем одиночный рейс
         * И всю информацию по нем. Так же возвращаем клавиатуру со статусом отслеживания
         */
        $myList = "";
        if ($messagePiece[0] == "flight") {
            $number = $messagePiece[1];
            $date = $messagePiece[2];
            $page = $messagePiece[3];


//            $answer_text = $callback_data;
            $getApi = new GetApi();
            $fligtsData = $getApi->getFlightsByDate($date, $page);
//            dd($fligtsData);
            if (isset($fligtsData->code)) {
                if ($fligtsData->code == 404) {


                    $answer = Lang::get("messages.NoFlightsOnThisDate", [], $this->chat->lang);


                }
                if ($fligtsData->code == 500) {
                    $answer = Lang::get("messages.serverError", [], $this->chat->lang);
//

                }

            }

//            dd($date,$callback_data);


            if (isset($fligtsData["flights"])) {
                $flights = new Collection($fligtsData["flights"]);
                $flight = $flights->where("flight_number", $number)->first();
//                dd($flight);
                $keyboard = (new FlightKeyboard())->createCardButtons($flight, $page, $date, $user->getId(), $this->chat->lang);
                Log::error(\GuzzleHttp\json_encode($messagePiece));
                if (isset($messagePiece[6])) {

                    if ($messagePiece[6] == "myList") {
                        $keyboard = (new FlightKeyboard())->createCardButtons($flight, $page, $date, $user->getId(), $this->chat->lang, "myList");


                    }
                }
                $keyboard = $keyboardMessage->setKeyboard($keyboard);


                $answer = GetMessageFromData::generateCard($flight, $this->chat->lang);
            }
        }

        if ($messagePiece[0] == 'track') {
            $date = $messagePiece[1];
            $page = $messagePiece[2];
            $flight_number = $messagePiece[3];
            $status = $messagePiece[4];


            $getApi = new GetApi();
            $flight = $getApi->getOneFlight($date, $flight_number, $page);

//            dd($flight);
            $code = FlightHelper::GetStatus($flight, $this->chat->lang)->code;
            if ($code == 2 && $code == 3) {
                $answer = Lang::get("messages.thisFlightAlreadyArrived", [], $this->chat->lang);
            }
            if ($flight["delay"] != "0") {
                $expired_at = date("Y-m-d H:i:s", strtotime($flight["arrival_date"]) + $flight['delay']);
                $expired_at_utc = date("Y-m-d H:i:s", strtotime($flight["arrival_date_utc"]) + $flight['delay']);

            } else {
                $expired_at = $flight["arrival_date"];
                $expired_at_utc = $flight["arrival_date_utc"];
            }

            if (!$page) {
                $page = 1;
            }

            $createFlightTracking = FlightTracking::updateOrCreate([
                "date" => $date,
                "type" => "viber",
                "chat_id" => $user->getId(),
                "person_id" => $user->getId(),
                "flight_number" => $flight_number,
                'carrier' => $flight["carrier"],
            ],
                [
                    "page" => "$page",
                    "fromJSON" => \GuzzleHttp\json_encode($flight["from"]),
                    "toJSON" => \GuzzleHttp\json_encode($flight["to"]),
                    "status" => $status,
                    "delay" => $flight["delay"],
                    "expired_at" => $expired_at,
                    "expired_at_utc" => $expired_at_utc,
                    "departure_date" => $flight["departure_date"],
                    "arrival_date" => $flight["arrival_date"],
                    "departure_date_utc" => $flight["departure_date_utc"],
                    "arrival_date_utc" => $flight["arrival_date_utc"],
                ]);


            $keyboard = (new FlightKeyboard())->createCardButtons($flight, $page, $date, $user->getId(), $this->chat->lang);
//
            if (isset($messagePiece[5])) {
                $keyboard = (new FlightKeyboard())->createCardButtons($flight, $page, $date, $user->getId(), $this->chat->lang, "myList");

            }
            $keyboard = $keyboardMessage->setKeyboard($keyboard);
            $from = (array)$flight["from"];
            $to = (array)$flight["to"];
            $lang = $this->chat->lang;
            if ($lang == "uk")
                $lang = 'ua';
            if ($status == 1) {


                $answer = Lang::get("messages.answerForTrack", [], $this->chat->lang) .
                    "\n" . $flight["carrier"] . "-" . $flight_number . " " . $from[$lang] . "-" . $to[$lang];

            } else {
                $answer = Lang::get("messages.answerForTrackStop", [], $this->chat->lang) .
                    "\n" . $flight["carrier"] . "-" . $flight_number . " " . $from[$lang] . "-" . $to[$lang];

            }
//            dd($creteFlightTracking->wasRecentlyCreated);
//            dd($createFlightTracking);
        }
        if ($messagePiece[0] == $this->getTitle('buttons.myFlightList') || $messagePiece[0] == "mylist" || $messagePiece[0] == "BackToMyList") {


            $page = 1;
            if (isset($messagePiece[1])) {
                $date = $messagePiece[1];
            }
            if (isset($messagePiece[2])) {
                $page = $messagePiece[2];
            }
            $answer = Lang::get('messages.FlightsListText',[],$this->chat->lang);
            if ($messagePiece[0] == "mylist") {
                $answer = "$date \n page #$page ";
                if ($page < 1) {
                    $answer = Lang::get("messages.YouAlreadyAtStart", [], $this->chat->lang);
                    $page = 1;
                } elseif ($page > $messagePiece[4]) {
                    $answer = Lang::get("messages.listIsOver", [], $this->chat->lang);
                    $page = $messagePiece[4];
                }
            }
            $usersTracksFlights = FlightTracking::where("status", 1)->where('chat_id', $user->getId())->get();

            $keyboard = new MyListKeyboard($user->getId());

            $keyboard = $keyboard->flights($usersTracksFlights, $user->getId(), $page);
            $checkKeyboard=$keyboard->getKeyboard();
            if ( count($checkKeyboard->Buttons)<=1){
             $answer=Lang::get('messages.emptyFlightsList',[],$this->chat->lang);
            }
        }


//        $newKeyboard = $keyboard->setKeyboard((new Keyboard($buttons)));
        /**
         * Ответ если не совпало ни с чем (такое может быть крайне редко)
         * Нужно для всех событий добавлять ответ чтоб не получать это сообщение
         */
        if (!$answer) {

            $keyboard = new MainMenu($user->getId());
            $keyboard = $keyboard->getKeyboard($this->chat->lang);
            $answer = "SORRY I DONT UNDERSTAND WHAT U WANT";
//            $answer=$text;
        }
        /**
         * Смотрим будем мы возвращать сообщение с клавиатурой или без нее
         */
        if ($keyboard) {
            $bot = new Bot($this->getRequest(), $keyboard);
            $bot->on(new MessageEvent($this->getRequest()->timestamp, $this->getRequest()->message_token,
                $user, $this->getRequest()->message))
                ->replay(
                    $answer
                )
                ->send();

        } else {
            $bot = new Bot($this->getRequest(), new TextMessage());
            $bot->on(new MessageEvent($this->getRequest()->timestamp, $this->getRequest()->message_token,
                $user, $this->getRequest()->message))
                ->replay(
                    $answer
                )
                ->send();
        }


    }

    public function closeConvers($user_id)
    {
        ViberConversation::where('user_id', $user_id)
            ->where('chat_id', $user_id)
            ->delete();

    }

    public function getTitle($button)
    {
        $item = MenuItem::where('id', telegram_config_no_translate($button))->
        first();

        $item = $item->translate($this->chat->lang);

        return $item['title'];
    }
}
