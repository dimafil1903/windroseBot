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

use App\CheckedByUser;
use App\DistributionType;
use App\FlightTracking;
use App\MenuItem;
use App\ShopCategory;
use App\ShopProduct;
use App\Telegram\Helpers\ConvertDate;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;
use App\Telegram\keyboards\CartKeyboard;
use App\Telegram\keyboards\CreateInlineKeyboard;
use App\Telegram\keyboards\InlineCategories;
use App\Telegram\keyboards\InlineProduct;
use App\Telegram\keyboards\LangInlineKeyboard;
use App\Telegram\keyboards\MailInlineKeyboard;
use App\Telegram\keyboards\NewProductsInlineKeyboard;
use App\Telegram\keyboards\OrderKeyboard;
use App\TelegramDistribution;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use App\Chat;
use App\TelegramSetting;
use TCG\Voyager\Models\Setting;
use App\TelegramCart;

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
        $answer_text = $callback_data;


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

            $fligtsData = GetApi::getFlightsByDate($date, $page);
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
            $keyboard = $flightsList->createFlightsList($fligtsData);
            $data = array(
                "chat_id" => $chat_id,
                "message_id" => $callback_message_id,
                "reply_markup" => $keyboard,
            );
            if (isset($fligtsData->current_page) && isset($fligtsData->last_page)) {
                $answer_text = Lang::get("messages.list", [], "$chat->lang") . " $fligtsData->current_page " . Lang::get("messages.of", [], "$chat->lang") . " $fligtsData->last_page";

                $data["text"] = ConvertDate::ConvertToWordMonth($date, $chat->lang) . "\n" . $answer_text;

            }


            if ($page < 1) {
                $answer_text = Lang::get("messages.YouAlreadyAtStart", [], "$chat->lang");
            } elseif ($page > $fligtsData->last_page) {
                $answer_text = Lang::get("messages.listIsOver", [], "$chat->lang");
            }

            Request::editMessageReplyMarkup($data);
            Request::editMessageText($data);
        } elseif ($callbackPiece[0] == 'flight') {
            $number = $callbackPiece[1];
            $date = $callbackPiece[2];
            $page = $callbackPiece[3];
            $answer_text = $callback_data;
            $fligtsData = GetApi::getFlightsByDate($date, $page);
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
            }
            if (isset($fligtsData->data)) {
                $flights = new Collection($fligtsData->data);
                $flight = $flights->where("flight_number", $number)->first();
                $data = array(
                    "chat_id" => $chat_id,
                    'message_id' => $callback_message_id,
                    "text" => GetMessageFromData::generateCard($flight, $chat->lang),
                    "reply_markup" => $flightsList->createCardButtons($flight, $page, $date, $chat->lang)
                );
                 Request::editMessageReplyMarkup($data);
                 Request::editMessageText($data);
            }
        }
        if ($callbackPiece[0] == 'track') {
            $date = $callbackPiece[1];
            $page = $callbackPiece[2];
            $flight_number = $callbackPiece[3];
            $status = $callbackPiece[4];
            $flight = GetApi::getOneFlight($date, $page, $flight_number);
//            dd($flight);


            $createFlightTracking = FlightTracking::updateOrCreate([
                "date" => $date,
                "page" => $page,
                "chat_id" => $chat_id,
                "person_id" => $callback_query->getFrom()->getId(),
                "flight_number" => $flight_number,
                'carrier'=>$flight->carrier,
            ],
                [
                    "fromJSON"=>\GuzzleHttp\json_encode($flight->from),
                    "toJSON"=>\GuzzleHttp\json_encode($flight->to),
                    "status" => $status,

                    "departure_date" => $flight->departure_date,
                    "arrival_date" => $flight->arrival_date,
                ]);

            $data = array(
                "chat_id" => $chat_id,
                'message_id' => $callback_message_id,
                "text" => GetMessageFromData::generateCard($flight, $chat->lang),
                "reply_markup" => $flightsList->createCardButtons($flight, $page, $date, $chat->lang)
            );
            Request::editMessageReplyMarkup($data);
            Request::editMessageText($data);
//            dd($creteFlightTracking->wasRecentlyCreated);
//            dd($createFlightTracking);
        }

        /**
         * Манипуляции с выбором языком
         *
         */
        if ($callbackPiece[0] == 'lang') {
            if (!empty($callbackPiece[1])) {
                $lang = $callbackPiece[1];
            }
            $lang_menu = new LangInlineKeyboard($chat_id);
            if (isset($lang)) {
                $lang_menu->set($lang, $callback_message_id);
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
