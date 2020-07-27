<?php


namespace App\Telegram\Helpers;


use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Lang;

class FlightHelper
{

    static function GetStatus($flight, $lang)
    {
        $currentTime = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));
        $departure_date = new DateTime($flight["departure_date"], new DateTimeZone('Europe/Kiev'));
        $arrival_date = new DateTime($flight["arrival_date"], new DateTimeZone('Europe/Kiev'));

        $status = "";
//        $currentTime=strtotime(date("Y-m-d H:i:s"));
        $code = 0;
        if ($flight["delay"] == "0") {
            $status = Lang::get("messages.scheduled", [], "$lang");
            $code = 0;
        }
        if ($currentTime > $arrival_date) {
            $status = Lang::get("messages.arrived", [], "$lang");
            $code = 2;
        }
        if ($currentTime > $departure_date && $currentTime < $arrival_date) {
            $status = Lang::get("messages.inFlight", [], "$lang");
            $code = 1;
        }
        if ($code == 2 && $flight["delay"] != "0") {
            $status = Lang::get("messages.arrived", [], "$lang") . ", " . Lang::get("messages.delay", [], "$lang") . ": +" . date("H:i", $flight->delay);
            $code = 3;
        }
        if ($code == 1 && $flight["delay"] != "0") {
            $status = Lang::get("messages.inFlight", [], "$lang") . ", " . Lang::get("messages.delay", [], "$lang") . ": +" . date("H:i", $flight->delay);
            $code = 4;
        }
        return (object)["message" => $status, "code" => $code];
    }

    /**
     * @param $flight
     * @return false|string
     */
    static function GetTimeInFlight($flight)
    {
        $departure = strtotime($flight["departure_date_utc"]);
        $arrival = strtotime($flight["arrival_date_utc"]);
        return date("H:i", $arrival - $departure);
    }

}
