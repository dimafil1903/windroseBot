<?php


namespace App\Telegram\Helpers;


class FlightHelper
{

    static function GetStatus($flight)
    {
        $status="";
        $currentTime=strtotime(date("Y-m-d H:i:s"));
        $code=0;
        if ($flight->delay=="0"){
            $status="Scheduled";
            $code=0;
        }
        if ($currentTime>strtotime($flight->arrival_date)){
            $status="Arrived";
            $code=2;
        }
        if ($currentTime>strtotime($flight->departure_date)&&$currentTime<strtotime($flight->arrival_date) ){
            $status="InFlight";
            $code=1;
        }
        if ($flight->delay!=="0"){
            $status.=" delay: +".date("H:i",$flight->delay);
            $code=3;
        }
        return (object)["message"=>$status,"code"=>$code];
    }

    /**
     * @param $flight
     * @return false|string
     */
    static function GetTimeInFlight($flight){
        $departure = strtotime($flight->departure_date);
        $arrival = strtotime($flight->arrival_date);
        return date("H:i", $arrival - $departure);
    }

}
