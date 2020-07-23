<?php


namespace App\Telegram\keyboards;


use App\Chat;
use App\FlightTracking;
use App\Telegram\Helpers\FlightHelper;
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
        if (isset($data->code))
        return false;
//        if (isset($date->date))
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
                    'callback_data' => "flight_$datum->flight_number" . "_$date" . "_$current_page"]]);
        }


        $miniArray = [
            ['text' => "⬅️ " . Lang::get("messages.back", [], "$chat->lang"), 'callback_data' => "flightsPage_$date" . "_$prev"],
            ['text' => Lang::get("messages.next", [], "$chat->lang") . " ➡️", 'callback_data' => "flightsPage_$date" . "_$next"]
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

    public function createCardButtons($flight, $page, $date, $lang, $type = "list")
    {
        try {
            $flightInDB = FlightTracking::where('page', $page)
                ->where("date", $date)
                ->where("chat_id", $this->chat_id)
                ->where("flight_number", $flight->flight_number)
                ->first();
            $isEnabled = "";
            $track = Lang::get("messages.track", [], "$lang");
            if ($flightInDB) {
                if ($flightInDB->status == 0) {
                    $status = 1;
                } else {
                    $isEnabled = "❌";
                    $track = Lang::get("messages.tracking", [], "$lang");
                    $status = 0;
                }
            } else {
                $status = 1;
            }

            $keyboard = [];
//            dd(FlightHelper::GetStatus($flight)->code);
            if (FlightHelper::GetStatus($flight, $lang)->code !== 2 && FlightHelper::GetStatus($flight, $lang)->code !== 3) {
                if ($type=="myList") {
                    array_push($keyboard, [['text' => $track . " $isEnabled", 'callback_data' => "track_$date" . "_$page" . "_$flight->flight_number" . "_$status" . "_myList"]]);
                }elseif ($type == "list"){
                    array_push($keyboard, [['text' => $track . " $isEnabled", 'callback_data' => "track_$date" . "_$page" . "_$flight->flight_number" . "_$status" ]]);

                }
                }
            array_push($keyboard, [['text' => Lang::get("messages.share", [], "$lang"), 'switch_inline_query' => "$flight->carrier-$flight->flight_number $date"]]);
            if ($type == "list") {
                array_push($keyboard, [['text' => Lang::get("messages.backToList", [], "$lang"), 'callback_data' => "backToFlightList_$date" . "_$page"]]);
            }else if ($type=="myList"){
                array_push($keyboard, [['text' => Lang::get("messages.backToList", [], "$lang"), 'callback_data' => "backToMyFlightList"]]);

            }

            return new InlineKeyboard($keyboard);
        } catch (TelegramException $e) {
        }

    }

    public function createMyFlightsList($data)
    {
        $chat = Chat::find($this->chat_id);
        $array = [];


//        $last_page=$data->last_page;
//        $prev = $current_page - 1;
//        $next = $current_page + 1;
        foreach ($data as $datum) {
//            dd($datum->fromJSON);
            $datum->from = \GuzzleHttp\json_decode($datum->fromJSON);
            $datum->to = \GuzzleHttp\json_decode($datum->toJSON);
            $from = (array)\GuzzleHttp\json_decode($datum->fromJSON);
            $to = (array)\GuzzleHttp\json_decode($datum->toJSON);
            $lang = "en";
            if ($chat->lang == "uk") {
                $lang = "ua";
            }

            array_push($array,
                [['text' =>
                    $datum->carrier . "-" .
                    $datum->flight_number . " " .
                    $from["$lang"] . "-" .
                    $to["$lang"],
                    'callback_data' => "flight_$datum->flight_number" . "_$datum->date" . "_$datum->page"."_myList"]]);
        }


//        $miniArray = [
//            ['text' => "⬅️ ".Lang::get("messages.back",[],"$chat->lang"), 'callback_data' => "flightsPage_$date" . "_$prev"],
//            ['text' => Lang::get("messages.next",[],"$chat->lang")." ➡️", 'callback_data' => "flightsPage_$date" . "_$next"]
//        ];
//        array_push($array, $miniArray);
//        dd($data->data);
        try {
            $inline_keyboard = new InlineKeyboard($array);
        } catch (TelegramException $e) {
            Log::error('Something is really going wrong.');

        }
        return $inline_keyboard;
    }

    public function CreateMyListButton(){

    }
}
