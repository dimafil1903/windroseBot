<?php


namespace App\Viber\Keyboards;


use App\FlightTracking;
use App\ViberUser;
use DateTime;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;

class MyListKeyboard
{

    public $chat_id;
    public function __construct($chat_id)
    {
        $this->chat_id=$chat_id;
    }


    public function getMyList(){

                 }
    public function getButtons($data, $chat_id, $current_page = "", $fieldStatus = "hidden",$myList=false)
    {
        if (isset($data->code))
            if ($data->code != "0")
                return false;
//        if (isset($date->date))
//        dd($data["date"]);

//        dd($date);
//        $current_page = $data->current_page;
        $chat = ViberUser::where("user_id", $chat_id)->first();
        if ($data->isEmpty()){
            $buttons[] = (new Button("reply",
                "BackToSchedule",
                "<font color='#FFFFFF'>" .
                Lang::get('messages.back', [], $chat->lang) .
                "</font>",
                "regular"))
                ->setColumns(6)
                ->setRows(1)
                ->setBgColor("#8176d6");
            return $buttons;
        }


        if (!$current_page) $current_page = 1;

        if (isset($data["last_page"])) {
            $last_page = $data["last_page"];
        }else{
            $last_page=(int) ceil((count($data)/10));
        }
        $prev = $current_page - 1;
        if ($prev <= 0) {
            $prev = 0;
        }
        $next = $current_page;
        if ($current_page != $last_page+1) {
            $next = $current_page + 1;
        }

        $data = (object)$data;
//       Log::emergency(\GuzzleHttp\json_encode($data));
        $flights = $data->forPage($current_page, 10);
        $buttons = [];

        $lang = "en";
        if ($chat->lang == "uk") {
            $lang = "ua";
        }
        foreach ($flights as $datum) {

            $from = \GuzzleHttp\json_decode($datum->fromJSON);
            $to = \GuzzleHttp\json_decode($datum->toJSON);
            $date = $datum->date;
            Log::emergency(\GuzzleHttp\json_encode($data));
            $time = new DateTime($datum["departure_date"]);
            $time = $time->format("H:i");
            $time2 = new DateTime($datum["arrival_date"]);
            $time2 = $time2->format("H:i");

            $buttons[] = (new Button("reply",
                "flight_" . $datum["flight_number"] . "_$date" . "_$current_page" . "_$fieldStatus"."_".$last_page."_myList",
                "<font color='#FFFFFF'>" .
                $datum["carrier"] . "-" . $datum["flight_number"] . "\n" .
                $from->$lang . " " . $time . " - " .

                $to->$lang . " " .
                $time2 . "</font>",
                "regular"))
                ->setColumns(6)
                ->setRows(1)
                ->setBgColor("#8176d6");


        }
        $buttons[] = (new Button("",
            "mylist_$date" . "_$prev" . "_$fieldStatus" . "_$last_page", ""
        ))->setImage("https://windrosebot.dimafilipenko.website/storage/photo/left-arrow%20(1).png")
            ->setColumns(2)
            ->setRows(1)
            ->setTextHAlign("right")
            ->setSilent(true)
            ->setBgColor("#8176d6");;
        $buttons[] = (new Button("reply",
            "BackToSchedule",
            "<font color='#FFFFFF'>" .
            Lang::get('messages.back', [], $chat->lang) .
            "</font>",
            "regular"))
            ->setColumns(2)
            ->setRows(1)
            ->setBgColor("#8176d6");
        $buttons[] = (new Button("reply",
            "mylist_$date" . "_$next" . "_$fieldStatus" . "_$last_page", ""
        ))->setSilent(true)->setActionType("reply")
            ->setTextHAlign("left")
            ->setBgMedia("https://windrosebot.dimafilipenko.website/storage/photo/next%20(2).png")
            ->setColumns(2)
            ->setRows(1)
            ->setBgColor("#8176d6");
        return $buttons;
    }

    public function flights($data, $chat_id, $current_page = "", $fieldState = "hidden",$myList=false)
    {


        $usersTracksFlights = FlightTracking::where("status", 1)
            ->where('type','viber')
            ->where('chat_id', $this->chat_id)->get();

       $buttons= $this->getButtons($usersTracksFlights,$chat_id,$current_page);

        $keyboard = new KeyboardMessage();

        return $keyboard->setKeyboard((new Keyboard($buttons))->setInputFieldState($fieldState));
    }

}
