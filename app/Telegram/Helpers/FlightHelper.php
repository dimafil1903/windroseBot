<?php


namespace App\Telegram\Helpers;


use DateTime;
use Exception;
use Illuminate\Support\Facades\Lang;


class FlightHelper
{
    /**
     * @param $flight
     * @param $lang
     * @return object
     * @throws Exception
     * Получаем статус рейса (за графиком, прибыл, в полете, задержка + какой-то из предыдущих)
     */
    static function GetStatus($flight, $lang)
    {
        $currentTime = new DateTime('NOW');
        $departure_date = new DateTime($flight["departure_date_utc"]);
        $arrival_date = new DateTime($flight["arrival_date_utc"]);

        $status = "";

        $code = 0;

        if ($flight["delay"] == "0") {
            $status = Lang::get("messages.scheduled", [], "$lang");
            $code = 0;
        }
        if ($currentTime > $arrival_date) {
            $status = Lang::get("messages.arrived", [], "$lang");
            $code = 2;
//            dd("D");
        }
        if ($currentTime > $departure_date && $currentTime < $arrival_date) {
            $status = Lang::get("messages.inFlight", [], "$lang");
            $code = 1;
        }
        if ($code == 2 && $flight["delay"] != "0") {
            $status = Lang::get("messages.arrived", [], "$lang") . ", " . Lang::get("messages.delay", [], "$lang") . ": +" . date("H:i", $flight["delay"]);
            $code = 3;
        }
        if ($code == 1 && $flight["delay"] != "0") {
            $status = Lang::get("messages.inFlight", [], "$lang") . ", " . Lang::get("messages.delay", [], "$lang") . ": +" . date("H:i", $flight["delay"]);
            $code = 4;
//            Log::notice(\GuzzleHttp\json_encode([$lang,$code]));
        }
        if ($code == 0 && $flight["delay"] != "0") {
            $status =  Lang::get("messages.delay", [], "$lang") . ": +" . date("H:i", $flight["delay"]);
            $code = 5;
//            Log::notice(\GuzzleHttp\json_encode([$lang,$code]));
        }

//        dd($flight,$currentTime,$departure_date,$arrival_date,$status, $code);
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

    static function telegram_config($key, $lang)
    {
        $config = \App\TelegramConfig::where('key', $key)->first();
        $config = $config->translate($lang);
        return $config->value;
    }


    static function telegram_config_no_translate($key)
    {
        $config = \App\TelegramConfig::where('key', $key)->first();

        return $config->value;
    }


}
