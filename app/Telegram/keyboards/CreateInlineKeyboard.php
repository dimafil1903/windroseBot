<?php


namespace App\Telegram\keyboards;


use App\Chat;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;

class CreateInlineKeyboard
{
    private $chat_id;

    public function __construct($chat_id)
    {
        $this->chat_id = $chat_id;
    }

    public function createFlightsList($data)
    {
        $chat = Chat::find($this->chat_id);
        $array = [];
        $date = $data->date;
        $current_page = $data->current_page;
//        $last_page=$data->last_page;
        $prev = $current_page - 1;
        $next = $current_page + 1;
        foreach ($data->data as $datum) {
            $from = (array)$datum->from;
            $to = (array)$datum->to;
            $lang = "en";
            if ($chat->lang == "uk") {
                $lang = "ua";
            }
            array_push($array,
                [['text' =>
                    $datum->carrier . "-" .
                    $datum->flight_number . " " .
                    $from[$lang] . "-" .
                    $to[$lang],
                    'callback_data' => "flight_$datum->flight_number"."_$date" . "_$current_page"]]);
        }


        $miniArray = [
            ['text' => "⬅️ ".Lang::get("messages.back",[],"$chat->lang"), 'callback_data' => "flightsPage_$date" . "_$prev"],
            ['text' => Lang::get("messages.next",[],"$chat->lang")." ➡️", 'callback_data' => "flightsPage_$date" . "_$next"]
        ];
        array_push($array, $miniArray);
//        dd($data->data);
        try {
            $inline_keyboard = new InlineKeyboard($array);
        } catch (TelegramException $e) {
            Log::error('Something is really going wrong.');

        }
        return $inline_keyboard;
    }
    public function createCardButtons($flight,$page,$date){
        try {
            $inline_keyboard = new InlineKeyboard([
                [['text' => "MONITOR", 'callback_data' => "monitor_$flight->flight_number"]],
                [['text' => 'inline', 'switch_inline_query' => $flight->flight_number],],
                [['text' => "GO BACK TO MENU", 'callback_data' => "backToFlightList_$date"."_$page"]]
            ]);
            return $inline_keyboard;
        } catch (TelegramException $e) {
        }

    }
}
