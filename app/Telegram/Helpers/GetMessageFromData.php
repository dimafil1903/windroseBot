<?php


namespace App\Telegram\Helpers;


class GetMessageFromData
{


    static function generateCard($flight,$lang){
        if ($lang=="uk"){
            $lang="ua";
        }
        $from=(array) $flight->from;
        $to=(array) $flight->to;
        $fleet= (array) $flight->fleet;
        $text="flight_number: $flight->carrier-$flight->flight_number";
        $text.="\nfrom: ".$from["$lang"]."  ";
        if (!empty($flight->from_terminal)) {
            $text.="from_terminal: $flight->from_terminal";
        }
        $text.="\nto: ".$to["$lang"]."  ";
        if (!empty($flight->to_terminal)) {
            $text.="to_terminal: $flight->to_terminal";
        }
        $text.="\ndeparture_date: ".date( "d.m.Y", strtotime( "$flight->departure_date" ) ) ;
        $text.="\ndeparture_time: ".date( "h:i", strtotime( "$flight->departure_date" ) ) ;
        $text.="\narrival_date: ".date( "d.m.Y", strtotime( "$flight->arrival_date" ) )  ;
        $text.="\narrival_time: ".date( "h:i", strtotime( "$flight->arrival_date" ) )  ;

        return $text;
    }
}
