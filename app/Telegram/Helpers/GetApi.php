<?php


namespace App\Telegram\Helpers;


use App\Http\Controllers\FlightsByDateController;
use Illuminate\Support\Collection;

class GetApi
{
    /**
     * @param $date
     * @param $page
     * @return mixed
     */
    static function getFlightsByDate($date, $page)
    {
        $c = new FlightsByDateController;
        $request = env('APP_URL', null) . "/api/getFlightsByDate?date=$date&page=$page";
        $result = $c->getApiData($request);

//        dd($result);
//        if (isset($result->code)) {
//            if ($result->code==404)
//              return null;
//        }
        return $result ? $result : false;

    }

    /**
     * @param $date
     * @param $page
     * @param $number
     * @return mixed
     */
    static function getOneFlight($date,$page,$number)
    {
      $data=  self::getFlightsByDate($date,$page);
      if ($data) {
          $flights = new Collection($data->data);
          return $flights->where("flight_number", $number)->first();
      }
    }

    /**
     * @param $number
     * @return mixed
     * FUTURE FUNCTION
     */
    static function getFlightByNumber($number)
    {
//        $data=  self::getFlightsByDate();
//        $flights = new Collection($data->data);
//        return $flights->where("flight_number", $number)->first();
    }
}
