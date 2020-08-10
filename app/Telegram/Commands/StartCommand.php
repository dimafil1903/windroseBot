<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\FlightTracking;
use App\MenuItem;
use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;
use App\Telegram\keyboards\ContactsKeyboard;
use App\Telegram\keyboards\InlineCategories;
use App\Telegram\keyboards\LangInlineKeyboard;
use App\TelegramUser;
use App\Viber\Keyboards\ContactKeyboard;
use Illuminate\Support\Facades\Lang;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use App\Telegram\keyboards\MainKeyboard;
use App\Chat;
use Longman\TelegramBot\Conversation;
use App\TelegramSetting;

class StartCommand extends UserCommand
{
    protected $name = 'start';
    protected $usage = '/start';
    protected $description = "Start Command";
    protected $conversation;

    public function execute()
    {
        $message = $this->getMessage();
        $keyboard = new MainKeyboard;

        $chat_id = $message->getChat()->getId();
        $user = $message->getFrom();
        $user_id = $user->getId();


        $params = $message->getText(true);
        // Проверяем установленный ли язык (при первом запуске должен быть пустым)
        $default_lang = 'ru';// На случай если будет язык по умолчанию
        $chat = Chat::find($chat_id);

//        dd($chat);

//       dd($params);
        $paramPieces = explode("_", $params);
        $userTg = TelegramUser::find($chat_id);
        if ($paramPieces[0] == "track") {
            $date = $paramPieces[1];
            $lang = $paramPieces[2];
            $firstTime = false;
            if (!$chat->lang) {
                $firstTime = true;
                $chat->lang = $lang;
                $chat->save();
            }

            $flight_number = $paramPieces[3];
            $status = $paramPieces[4];


            $getApi = new GetApi();
            $flight = $getApi->getOneFlight($date, $flight_number);

//            dd($flight);
//            dd($flight);
            if (isset($flight["code"])) {
                $code = $flight["code"];
            } else {
                $code = FlightHelper::GetStatus($flight, $chat->lang)->code;
            }

//            dd($code);
            if ($code == 2 || $code == 3 || $code == 404) {
                $answer_text = Lang::get("messages.thisFlightAlreadyArrived", [], "$chat->lang");
//                $data = [
//                    'chat_id' => $chat_id,
//                    'text' => $answer_text,
////                    'show_alert' => "thumb up",
////                    'cache_time' => 1,
//                ];
//                Request::sendMessage($data);
                $this->replyToChat($answer_text, ['reply_markup' => $keyboard = (new MainKeyboard())->getMainKeyboard($chat_id)]);
//                return Request::emptyResponse();
            } else {
                if ($flight["delay"] != "0") {
                    $expired_at = date("Y-m-d H:i:s", strtotime($flight["arrival_date"]) + $flight['delay']);
                    $expired_at_utc = date("Y-m-d H:i:s", strtotime($flight["arrival_date_utc"]) + $flight['delay']);

                } else {
                    $expired_at = $flight["arrival_date"];
                    $expired_at_utc = $flight["arrival_date_utc"];
                }

//            if (!$page){
//                $page=1;
//            }
                $createFlightTracking = FlightTracking::updateOrCreate([
                    "date" => $date,
                    "type" => "telegram",
                    "chat_id" => $chat_id,
                    "person_id" => $message->getFrom()->getId(),
                    "flight_number" => $flight_number,
                    'carrier' => $flight["carrier"],
                ],
                    [
                        "page" => 1,
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

                if ($status == 1 || $status == 5 || $status == 4) {
                    $from = (array)$flight["from"];
                    $to = (array)$flight["to"];

                    if ($lang == "uk")
                        $lang = 'ua';

                    $dataToSendMessage = [
                        "chat_id" => $chat_id,
                        "text" => Lang::get("messages.answerForTrack", [], "$chat->lang") .
                            "\n" . $flight["carrier"] . "-" . $flight_number . " " . $from[$lang] . "-" . $to[$lang],
                        'reply_markup' => $keyboard = (new MainKeyboard())->getMainKeyboard($chat_id)
                    ];
                    Request::sendMessage($dataToSendMessage);
                }
            }
            if (!$userTg->phone) {
//                $keyboard = $keyboard->getMainKeyboard($chat_id);
                $keyboard = (new ContactsKeyboard())->getKeyboard($chat->lang);
                $data = [
                    'reply_markup' => $keyboard,
                ];


                $this->replyToUser(Lang::get("messages.shareContactMessage", ["name" => $message->getFrom()->getFirstName(), "nameBot" => $message->getBotUsername()], "$chat->lang"),
                    $data);
            }

        } else {

            if (!$chat->lang) {
                $lang_menu = new LangInlineKeyboard($chat_id);
                $lang_menu->create_inline_menu(1);

            } else {


                if (!$userTg->phone) {
//                    $keyboard = $keyboard->getMainKeyboard($chat_id);
                    $keyboard = (new ContactsKeyboard())->getKeyboard($chat->lang);
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => Lang::get('messages.shareContactMessage', [], $chat->lang),
                        'reply_markup' => $keyboard,
                    ];

                    Request::sendMessage($data);

                } else {
                    $keyboard = $keyboard->getMainKeyboard($chat_id);

                    $data = [
                        'chat_id' => $chat_id,
                        'text' => Lang::get("messages.startMessage", ["name" => $message->getFrom()->getFirstName(), "nameBot" => $message->getBotUsername()], "$chat->lang"),
                        'reply_markup' => $keyboard,
                    ];
                    Request::sendMessage($data);
                }


            }
        }

    }
}
