<?php


namespace App\Telegram\Helpers;


use Illuminate\Support\Facades\Lang;

class GetMessageFromData
{


    static function generateCard($flight,$lang){
        $langForApi=$lang;
        if ($lang=="uk"){
            $langForApi="ua";
        }
        $from=(array) $flight->from;
        $to=(array) $flight->to;
        $fleet= (array) $flight->fleet;
        $text=Lang::get("messages.flight_number", [], "$lang")."$flight->carrier-$flight->flight_number";
        $text.="\n\n".Lang::get("messages.from", [], "$lang").$from["$langForApi"]."  ";
        if (!empty($flight->from_terminal)) {
            $text.=Lang::get("messages.from_terminal", [], "$lang").$flight->from_terminal;
        }
        $text.="\n".Lang::get("messages.to", [], "$lang").$to["$langForApi"]."  ";
        if (!empty($flight->to_terminal)) {
            $text.=Lang::get("messages.to_terminal", [], "$lang").$flight->to_terminal;
        }
        $text.="\n".Lang::get("messages.departure_date", [], "$lang").date( "d.m.Y", strtotime( "$flight->departure_date" ) ) ;
        $text.="\n".Lang::get("messages.departure_time", [], "$lang").date( "H:i", strtotime( "$flight->departure_date" ) ) ;
        $text.="\n".Lang::get("messages.arrival_date", [], "$lang").date( "d.m.Y", strtotime( "$flight->arrival_date" ) )  ;
        $text.="\n".Lang::get("messages.arrival_time", [], "$lang").date( "H:i", strtotime( "$flight->arrival_date" ) )  ;
        $text.="\n".Lang::get("messages.timeInFlight", [], "$lang").FlightHelper::GetTimeInFlight($flight);
        $text.="\n\n".Lang::get("messages.status", [], "$lang").FlightHelper::GetStatus($flight,$lang)->message;

        return $text;
    }
}
