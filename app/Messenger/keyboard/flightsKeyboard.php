<?php


namespace App\Messenger\keyboard;


use App\FlightTracking;
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
     * @param $user
     * @param null $date
     * @param bool $mylist
     * @return array
     * @throws Exception
     */
    public function create($data, $current_page, $user, $date = null, $mylist = false)
    {

        if (isset($data->code))
            if ($data->code != "0")
                return false;
//        if (isset($date->date))
//        dd($data["date"]);

//        dd($date);
//        $current_page = $data->current_page;

        $lang = $user->lang;
        $langForApi = $user->lang;
        if ($langForApi == "uk") {
            $langForApi = "ua";
        }
        if ($mylist) {
            $data = (object)$data;
//       Log::emergency(\GuzzleHttp\json_encode($data));
            $flights = $data->forPage($current_page, 10);
            $elements = [];
            foreach ($flights as $datum) {
                $flightInDB = FlightTracking::where('page', $current_page)
                    ->where("chat_id", $user->user_id)
                    ->where("flight_number", $datum["flight_number"])
                    ->first();
                $lang = $user->lang;
                $track = Lang::get("messages.track", [], "$lang");
                $isEnabled = "";
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
                if (!$current_page) $current_page = 1;
                $from = \GuzzleHttp\json_decode($datum->fromJSON);
                $to = \GuzzleHttp\json_decode($datum->toJSON);
                $date = $datum->date;
//            Log::emergency(\GuzzleHttp\json_encode($data));
                $time = new DateTime($datum["departure_date"]);
                $time = $time->format("H:i");
                $time2 = new DateTime($datum["arrival_date"]);
                $time2 = $time2->format("H:i");

                $text = "";
//        dd($flight);
           $text .= "\n👀" . Lang::get("messages.status", [], "$lang") . (FlightHelper::GetStatus($datum, "$lang"))->message;



                $el = Element::create(

                    $datum["carrier"] . "-" . $datum["flight_number"] . "\n" .
                    $from->$langForApi . " " . $time . " - " .

                    $to->$langForApi . " " .
                    $time2

                )
                    ->subtitle($text);
                if (FlightHelper::GetStatus($datum, $lang)->code !== 2 && FlightHelper::GetStatus($datum, $lang)->code !== 3) {
                    $el->addButton(ElementButton::create($track . " $isEnabled")
                        ->type("postback")
                        ->payload("track_" . $date . '_' . $current_page . "_" . $datum["flight_number"] . "_" . $status)
                    );

                }
                $el->addButton(ElementButton::create('подробней')
                    ->url('https://windrosebot.dimafilipenko.website')
                    ->heightRatio(ElementButton::RATIO_TALL)
                    ->enableExtensions());
                $elements[] = $el;

            }
        } else {


            $data = (object)$data;
            $flights = $data["flights"]->forPage($current_page, 10);


            $elements = [];


            foreach ($flights as $datum) {
                $flightInDB = FlightTracking::where('page', $current_page)
                    ->where("chat_id", $user->user_id)
                    ->where("flight_number", $datum["flight_number"])
                    ->first();
                $lang = $user->lang;
                $track = Lang::get("messages.track", [], "$lang");
                $isEnabled = "";
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
                if (!$current_page) $current_page = 1;
                $from = (array)$datum["from"];
                $to = (array)$datum["to"];


                $text = "";
//        dd($flight);
           $text .= "\n👀" . Lang::get("messages.status", [], "$lang") . (FlightHelper::GetStatus($datum, "$lang"))->message;
//        dd((FlightHelper::GetStatus($flight, "$lang"))->message);

                $time = new DateTime($datum["departure_date"]);
                $time = $time->format("H:i");
                $time2 = new DateTime($datum["arrival_date"]);
                $time2 = $time2->format("H:i");

                $el = Element::create(

                    $datum["carrier"] . "-" . $datum["flight_number"] . "\n" .
                    $from["$langForApi"] . " " . $time . " - " .

                    $to["$langForApi"] . " " .
                    $time2

                )
                    ->subtitle($text);
                if (FlightHelper::GetStatus($datum, $lang)->code !== 2 && FlightHelper::GetStatus($datum, $lang)->code !== 3) {
                    $el->addButton(ElementButton::create($track . " $isEnabled")
                        ->type("postback")
                        ->payload("track_" . $date . '_' . $current_page . "_" . $datum["flight_number"] . "_" . $status)
                    );

                }
                $el->addButton(ElementButton::create('подробней')
                    ->url('https://windrosebot.dimafilipenko.website')
                    ->heightRatio(ElementButton::RATIO_TALL)
                    ->enableExtensions());
                $elements[] = $el;


            }
        }

        return $elements;

    }

}
