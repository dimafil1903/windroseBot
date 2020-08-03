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


use App\FlightTracking;
use App\Telegram\Helpers\ConvertDate;
use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;
use App\Telegram\keyboards\CreateInlineKeyboard;
use App\Telegram\keyboards\LangInlineKeyboard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use App\Chat;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {

        $callback_query = $this->getCallbackQuery();
        $chat_id = $this->getCallbackQuery()->getMessage()->getChat()->id;
        $callback_query_id = $callback_query->getId();
        $callback_data = $callback_query->getData();
        $callback_message_id = $callback_query->getMessage()->getMessageId();


        /**
         *
         * ВАЖНЫЕ ПЕРЕМЕННЫЕ
         */

        $callbackPiece = explode("_", $callback_data);


        $chat = Chat::where('id', $chat_id)->first();


        /**
         * TEST CALLBACK DATA
         * NEED TO BE COMMENT IN PROD
         */
        $answer_text = null;
//        $answer_text = $callback_data;


        /**
         * Пагинация для списка рейсов
         */
        $data['chat_id'] = $chat_id;
        $flightsList = new CreateInlineKeyboard($chat_id);
        if ($callbackPiece[0] == 'flightsPage' || $callbackPiece[0] == "backToFlightList") {
            $date = "";
            $page = 1;
            if (isset($callbackPiece[1])) $date = $callbackPiece[1];
            if (isset($callbackPiece[2])) $page = $callbackPiece[2];

            if ($page<=0) $page=1;
            $getApi=new GetApi();
            $fligtsData = $getApi->getFlightsByDate($date, $page);
            if (isset($fligtsData->code)) {
                if ($fligtsData->code == 404) {
                    $data = [
                        "chat_id" => $chat_id,
                        "message_id" => $callback_message_id
                    ];
//                    $data['chat_id']=$chat_id;
//                    $data["message_id"] = $callback_message_id;

                    $answer_text = Lang::get("messages.NoFlightsOnThisDate", [], "$chat->lang");
                    $this->returnAnswerText($callback_query_id, $answer_text . $callback_message_id);
                    return Request::deleteMessage($data);
                }
            }
            $keyboard = $flightsList->createFlightsList($fligtsData,$page);
            if (!$keyboard){
                $answer_text = Lang::get("messages.ServerError", [], "$chat->lang");
                $data['text']=$answer_text;
                return Request::sendMessage($data);
            }
            $data = array(
                "chat_id" => $chat_id,
                "message_id" => $callback_message_id,
                "reply_markup" => $keyboard,
            );
            $lastPage=$getApi->getLastPage();
            if (isset($page) && $lastPage) {
                $answer_text = Lang::get("messages.list", [], "$chat->lang") . " $page " . Lang::get("messages.of", [], "$chat->lang") . " ".$lastPage;

                $data["text"] = ConvertDate::ConvertToWordMonth($date, $chat->lang) . "\n" . $answer_text;

            }


            if ($page < 1) {
                $answer_text = Lang::get("messages.YouAlreadyAtStart", [], "$chat->lang");
            } elseif ($page > $lastPage) {
                $answer_text = Lang::get("messages.listIsOver", [], "$chat->lang");
            }

            Request::editMessageReplyMarkup($data);
            Request::editMessageText($data);
        }

        elseif ($callbackPiece[0] == 'flight') {
            $number = $callbackPiece[1];
            $date = $callbackPiece[2];
            $page = $callbackPiece[3];
//            $answer_text = $callback_data;
            $getApi=new GetApi();
            $fligtsData = $getApi->getFlightsByDate($date, $page);
//            dd($fligtsData);
            if (isset($fligtsData->code)) {
                if ($fligtsData->code == 404) {

                    $data = [
                        "chat_id" => $chat_id,
                        "message_id" => $callback_message_id
                    ];
                    $answer_text = Lang::get("messages.NoFlightsOnThisDate", [], "$chat->lang");
                    $this->returnAnswerText($callback_query_id, $answer_text);
                    return Request::deleteMessage($data);
                }
                if ($fligtsData->code == 500) {
                    $answer_text = Lang::get("messages.serverError", [], "$chat->lang");
//
                    $data = [
                        "chat_id" => $chat_id,
                        "text" => $answer_text
                    ];
                      $this->returnAnswerText($callback_query_id, $answer_text);
                    return Request::sendMessage($data);
                }

            }
//            dd($date,$callback_data);
            if (isset($fligtsData["flights"])) {
                $flights = new Collection($fligtsData["flights"]);
                $flight = $flights->where("flight_number", $number)->first();
//                dd($flight);
                $replyMarkup = $flightsList->createCardButtons($flight, $page, $date, $chat->lang);

                if (isset($callbackPiece[4])) {
                    if ($callbackPiece[4] == "myList") {
                        $replyMarkup = $flightsList->createCardButtons($flight, $page, $date, $chat->lang, 'myList');

                    }
                }

                $data = array(
                    "chat_id" => $chat_id,
                    'message_id' => $callback_message_id,
                    "text" => GetMessageFromData::generateCard($flight, $chat->lang),
                    "reply_markup" => $replyMarkup,);
                Request::editMessageReplyMarkup($data);
                Request::editMessageText($data);
            }
        }
        if ($callback_data == "backToMyFlightList") {
//            dd("DD");
            $usersTracksFlights = FlightTracking::
            where("status", 1)
                ->where('chat_id', $chat_id)->get();


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
                    "message_id" => $callback_message_id,
                    "text" => Lang::get("messages.FlightsListText", [], "$chat->lang"),
                    "reply_markup" => $keyboard
                ];

                Request::editMessageText($data);
                Request::editMessageReplyMarkup($data);
            }
        }
        if ($callbackPiece[0] == 'track') {
            $date = $callbackPiece[1];
            $page = $callbackPiece[2];
            $flight_number = $callbackPiece[3];
            $status = $callbackPiece[4];


            $getApi=new GetApi();
            $flight = $getApi->getOneFlight($date, $flight_number, $page);

//            dd($flight);
            $code = FlightHelper::GetStatus($flight, $chat->lang)->code;
            if ($code == 2 && $code == 3) {
                $answer_text = Lang::get("messages.thisFlightAlreadyArrived", [], "$chat->lang");
                $data = [
                    'callback_query_id' => $callback_query_id,
                    'text' => $answer_text,
                    'show_alert' => "thumb up",
                    'cache_time' => 1,
                ];
                Request::answerCallbackQuery($data);
                return Request::emptyResponse();
            }
            if ($flight["delay"] != "0") {
                $expired_at = date("Y-m-d H:i:s", strtotime($flight["arrival_date"]) + $flight['delay']);
                $expired_at_utc = date("Y-m-d H:i:s", strtotime($flight["arrival_date_utc"]) + $flight['delay']);

            } else {
                $expired_at = $flight["arrival_date"];
                $expired_at_utc = $flight["arrival_date_utc"];
            }

            if (!$page){
                $page=1;
            }

            $createFlightTracking = FlightTracking::updateOrCreate([
                "date" => $date,
                "type"=>"telegram",
                "chat_id" => $chat_id,
                "person_id" => $callback_query->getFrom()->getId(),
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


            $replyMarkup = $flightsList->createCardButtons($flight, $page, $date, $chat->lang);

            if (isset($callbackPiece[5])) {
                $replyMarkup = $flightsList->createCardButtons($flight, $page, $date, $chat->lang, 'myList');

            }
            $data = array(
                "chat_id" => $chat_id,
                'message_id' => $callback_message_id,
                "text" => GetMessageFromData::generateCard($flight, $chat->lang),
                "reply_markup" => $replyMarkup);
            Request::editMessageReplyMarkup($data);
            Request::editMessageText($data);
            if ($status == 1) {
                $from = (array)$flight["from"];
                $to = (array)$flight["to"];
                $lang = $chat->lang;
                if ($lang == "uk")
                    $lang = 'ua';
                $dataToSendMessage = [
                    "chat_id" => $chat_id,
                    "text" => Lang::get("messages.answerForTrack", [], "$chat->lang") .
                        "\n".$flight["carrier"]."-".$flight_number." ". $from[$lang]."-".$to[$lang]
                ];
                Request::sendMessage($dataToSendMessage);
            }
//            dd($creteFlightTracking->wasRecentlyCreated);
//            dd($createFlightTracking);
        }

        /**
         * Манипуляции с выбором языком
         *
         */
        if ($callbackPiece[0] == 'lang') {

                if (isset($callbackPiece[1])) {
                    $lang = $callbackPiece[1];
                }
            $onStart="";
                if (isset($callbackPiece[2])) {
                    $onStart = $callbackPiece[2];
                }

//                dd($onStart);
            $lang_menu = new LangInlineKeyboard($chat_id);
            if (isset($lang)) {
                $lang_menu->set($lang, $callback_message_id);
            }
            if ($onStart=="1"){
                $chat=Chat::find($chat_id);
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Lang::get("messages.startMessage", ["name" => $callback_query->getFrom()->getFirstName(), "nameBot" => $callback_query->getBotUsername()], "$chat->lang"),

                ];
                return Request::sendMessage($data);
            }
        }


        /**
         * Ловим id Рассылки для клиента и возвращаем клавиатуру с выбором времени
         */
//        if($callbackPiece[0]=='dist'){
//                $mailInlineKeyboard= new MailInlineKeyboard($chat_id);
//                $mailInlineKeyboard->show_time_keyboard($callbackPiece[1],$callback_message_id);
//        }
//        if($callbackPiece[0]=='set'){
//         TelegramDistribution::updateOrInsert(
//             ['type_id'=>$callbackPiece[1],'chat_id'=>$chat_id],
//             ['time' => $callbackPiece[2]]
//         );
//         $dist=DistributionType::where('id',$callbackPiece[1])->get();
//         $dist=$dist->translate($chat->lang);
//            $answer_text='Рассылка '.$dist[0]->name.' Установлена на '.$callbackPiece[2];
//            Request::deleteMessage(['chat_id'=>$chat_id,'message_id'=>$callback_message_id]);
//            Request::SendMessage(['chat_id'=>$chat_id,'text'=>$answer_text]);
//        }
//        if($callbackPiece[0]=='deldist'){
//            TelegramDistribution::where('type_id',$callbackPiece[1])->where('chat_id',$chat_id)->delete();
//            $data=['chat_id'=>$chat_id,'message_id'=>$callback_message_id];
//Request::deleteMessage($data);
//        }
        if ($answer_text != null) {
            $data = [
                'callback_query_id' => $callback_query_id,
                'text' => $answer_text,
                'show_alert' => $callback_data === 'thumb up',
                'cache_time' => 1,
            ];
            Request::answerCallbackQuery($data);
        }

    }

    public function returnAnswerText($callback_query_id, $answer_text, $callback_data = 'thumb up')
    {
        $data = [
            'callback_query_id' => $callback_query_id,
            'text' => $answer_text,
            'show_alert' => $callback_data,
            'cache_time' => 1,
        ];
        Request::answerCallbackQuery($data);
    }
}
