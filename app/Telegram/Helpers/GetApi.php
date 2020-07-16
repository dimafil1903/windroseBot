<?php


namespace App\Telegram\Helpers;


use App\Http\Controllers\FlightsByDateController;

class GetApi
{
    static function getFlightsByDate($date,$page){
   $c= new FlightsByDateController;
   $request=env('APP_URL',null)."/api/getFlightsByDate?date=$date&page=$page";
   $result=$c->getApiData($request);
   if($result){
       return $result;
   }else{
       return false;
   }

    }
}
