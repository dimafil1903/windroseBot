<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\FlightTracking;
use App\MenuItem;
use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;
use App\Telegram\keyboards\InlineCategories;
use App\Telegram\keyboards\LangInlineKeyboard;
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
    protected $description="Start Command";
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


//       dd($params);
        $paramPieces = explode("_", $params);
        if ($paramPieces[0] == "track") {
            $date = $paramPieces[1];
            $lang = $paramPieces[2];
            $chat->lang = $lang;
            $chat->save();
            $flight_number = $paramPieces[3];
            $status = $paramPieces[4];


            $flight = GetApi::getOneFlight($date, $flight_number);
//            dd($flight);
            $code = FlightHelper::GetStatus($flight, $chat->lang)->code;
            if ($code == 2 || $code == 3) {
                $answer_text = Lang::get("messages.thisFlightAlreadyArrived", [], "$chat->lang")."🛬";
                $data = [
                    'chat_id' => $chat_id,
                    'text' => $answer_text,

                ];
                return Request::sendMessage($data);

            }
            if ($flight->delay != "0") {
                $expired_at = date("Y-m-d H:i:s", strtotime($flight->arrival_date) + $flight->delay);
            } else {
                $expired_at = $flight->arrival_date;
            }


            $createFlightTracking = FlightTracking::updateOrCreate([
                "date" => $date,
//                "page" => $page,
                "chat_id" => $chat_id,
                "person_id" => $message->getFrom()->getId(),
                "flight_number" => $flight_number,
                'carrier' => $flight->carrier,
            ],
                [
                    "fromJSON" => \GuzzleHttp\json_encode($flight->from),
                    "toJSON" => \GuzzleHttp\json_encode($flight->to),
                    "status" => $status,
                    "delay" => $flight->delay,
                    "expired_at" => $expired_at,
                    "departure_date" => $flight->departure_date,
                    "arrival_date" => $flight->arrival_date,
                ]);


            if ($status == 1) {
                $from = (array)$flight->from;
                $to = (array)$flight->to;
                $lang = $chat->lang;
                if ($lang == "uk")
                    $lang = 'ua';
                $dataToSendMessage = [
                    "chat_id" => $chat_id,
                    "text" => Lang::get("messages.answerForTrack", [], "$chat->lang") .
                        "\n$flight->carrier-$flight_number $from[$lang]-$to[$lang]"
                ];
             return   Request::sendMessage($dataToSendMessage);
            }
        }

        if (!$chat->lang) {
            $lang_menu = new LangInlineKeyboard($chat_id);
            $lang_menu->create_inline_menu();

        } else {

            $settings = TelegramSetting::first();
            $settings = $settings->translate($chat->lang);
//            $hello_text = $settings->start_message;
            $keyboard = $keyboard->getMainKeyboard($chat_id);
            $data = [
                'chat_id' => $chat_id,
                'text' => Lang::get("messages.startMessage", ["name" => $message->getFrom()->getFirstName(), "nameBot" => $message->getBotUsername()], "$chat->lang"),
                'reply_markup' => $keyboard,
            ];


           return Request::sendMessage($data);


        }


    }
}
