<?php


namespace App\Messenger\keyboard;


use App\Telegram\Helpers\FlightHelper;
use App\ViberUser;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\Element;
use BotMan\Drivers\Facebook\Extensions\ElementButton;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Lang;
use Paragraf\ViberBot\Model\Button;

class flightsKeyboard
{
    /**
     * @param $data
     * @param $current_page
     * @param $lang
     * @return array
     * @throws Exception
     */
    public function create($data, $current_page, $lang)
    {
        if (isset($data->code))
            if ($data->code != "0")
                return false;
//        if (isset($date->date))
//        dd($data["date"]);
        $date = $data["date"];
//        dd($date);
//        $current_page = $data->current_page;
        if (!$current_page) $current_page = 1;

        if (isset($data["last_page"])) {
            $last_page = $data["last_page"];
        } else {
            $last_page = (int)ceil((count($data) / 10));
        }
        $prev = $current_page - 1;
        if ($prev <= 0) {
            $prev = 0;
        }
        $next = $current_page;
        if ($current_page != $last_page + 1) {
            $next = $current_page + 1;
        }

        $data = (object)$data;
        $flights = $data["flights"]->forPage($current_page, 10);
        $buttons = [];


        $elements = [];


        foreach ($flights as $datum) {
            $from = (array)$datum["from"];
            $to = (array)$datum["to"];

            $langForApi = $lang;
            if ($langForApi == "uk") {
                $langForApi = "ua";
            }
            $text = "";
//        dd($flight);
            $text .= "\n🏙" . Lang::get("messages.from", [], "$lang") . $from["$langForApi"] . "  ";
            if (!empty($flight["from_terminal"])) {
                $text .= Lang::get("messages.from_terminal", [], "$lang") . $datum['from_terminal'];
            }
            $text .= "\n🌆" . Lang::get("messages.to", [], "$lang") . $to["$langForApi"] . "  ";
            if (!empty($flight["to_terminal"])) {
                $text .= Lang::get("messages.to_terminal", [], "$lang") . $datum['to_terminal'];
            }
            $text .= "\n📅" . Lang::get("messages.departure_date", [], "$lang") . date("d.m.Y", strtotime($datum['departure_date']));
            $text .= "\n🕐" . Lang::get("messages.departure_time", [], "$lang") . date("H:i", strtotime($datum['departure_date'])) . " " .
                Lang::get("messages.localTime", [], "$lang");
            $text .= "\n📆" . Lang::get("messages.arrival_date", [], "$lang") . date("d.m.Y", strtotime($datum['arrival_date']));
            $text .= "\n🕐" . Lang::get("messages.arrival_time", [], "$lang") . date("H:i", strtotime($datum['arrival_date'])) . " " .
                Lang::get("messages.localTime", [], "$lang");;
            $text .= "\n⏳" . Lang::get("messages.timeInFlight", [], "$lang") . FlightHelper::GetTimeInFlight($datum);
            $text .= "\n👀" . Lang::get("messages.status", [], "$lang") . (FlightHelper::GetStatus($datum, "$lang"))->message;
//        dd((FlightHelper::GetStatus($flight, "$lang"))->message);

            $time = new DateTime($datum["departure_date"]);
            $time = $time->format("H:i");
            $time2 = new DateTime($datum["arrival_date"]);
            $time2 = $time2->format("H:i");
            $elements[] = Element::create(

                $datum["carrier"] . "-" . $datum["flight_number"] . "\n" .
                $from["$langForApi"] . " " . $time . " - " .

                $to["$langForApi"] . " " .
                $time2

            )
                ->subtitle($text)
                ->addButton(ElementButton::create('Отслеживать')
                    ->url('https://github.com/mpociot/botman-laravel-starter')
                )
                ->addButton(ElementButton::create('подробней')
                    ->url('https://windrosebot.dimafilipenko.website')
                    ->heightRatio(ElementButton::RATIO_TALL)
                    ->enableExtensions()
        );


//            $buttons[] = (new Button("reply",
//                "flight_" . $datum["flight_number"] . "_$date" . "_$current_page" . "_$fieldStatus"."_$myList",
//                "<font color='#FFFFFF'>" .
//                $datum["carrier"] . "-" . $datum["flight_number"] . "\n" .
//                $from[$lang] . " " . $time . " - " .
//
//                $to[$lang] . " " .
//                $time2 . "</font>",
//                "regular"))
//                ->setColumns(6)
//                ->setRows(1)
//                ->setBgColor("#8176d6");


        }

        return $elements;

    }

}
