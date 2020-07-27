<?php


namespace App\Telegram\Helpers;


use App\Http\Controllers\FlightsByDateController;
use DateInterval;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use function Complex\sec;

class GetApi
{
    /**
     * @param $date
     * @param $page
     * @return mixed
     */
    public $dataInfo;
    public $lastPage;
    public function getFlightsByDate($date, $page = "")
    {

        $request = env('APP_URL', null) . "/storage/json/date/$date.json";
        $result = self::getApiData($request);
//        dd($date,$result);

        $this->dataInfo=$result;
        if ($this->dataInfo->status=='error'){
            return false;
        }
        /**
         * МАССИВ АЕРОПОРТОВ
         */
//        dd($this->dataInfo);
        $airports = collect($this->dataInfo->airports);
        /**
         * МАССИВ рейсов
         */
        $flights = collect($this->dataInfo->flights);
        $flightsCount = $flights->count();
        /**
         * МАССИВ самолетов
         */
        $fleets = collect($this->dataInfo->fleets);

        $returnData = new Collection();

        foreach ($flights as $flight) {
//
            $item = collect($flight);
            $item['from'] = $this->createArray($item['from'], $airports);
            $item['to'] = $this->createArray($item['to'], $airports);
            $item['fleet'] = $this->createArray($item['fleet'], $fleets);
            $returnData->add($item);
//               $this->getTime($item['departure_date']);
//               dd($item);
        }
        $date=$this->dataInfo->date;
//        dd($date);
        $lastPage = ceil($returnData->count() / 10);
        $All_INFO = new Collection();

        $All_INFO->put("last_page", $lastPage);
        $All_INFO->put("date", $date);
//        $All_INFO->put("current_page", $current_page);

//        if (!$withoutPagination)
//            $returnData = $returnData->forPage($current_page, 10);
        $All_INFO->put("flights", $returnData);
//        $date=$this->dataInfo->date;
//        dd($result);
//        dd($result);
//        dd($result);
//        if (isset($result->code)) {
//            if ($result->code==404)
//              return null;
//        }
//        dd($flights);
//        dd($All_INFO);
        return $All_INFO ? $All_INFO : false;

    }
    public function getLastPage(){
        $flights = new Collection($this->dataInfo->flights);
        $this->lastPage=ceil($flights->count() / 10);
        return $this->lastPage;
    }

    /**
     * @param $date
     * @param $page
     * @param $number
     * @return mixed
     */
    public function getOneFlight($date, $number, $page = "")
    {
        $data = $this->getFlightsByDate($date, $page);
//        dd($data);
        if ($data) {
            if (isset($data["flights"])) {
                $flights = new Collection($data["flights"]);
                $flight = $flights->where("flight_number", $number)->first();
                if ($flight) return $flight;
            }

        }
        return ["message" => "error", "code" => 404];
    }

    static function createJsonFromApi($that,$count = "")
    {
        $date = new DateTime('YESTERDAY');
        $date2 = new DateTime('NOW');
        $date2->sub(new DateInterval('P2D'));
//        echo $date2->format('Y-m-d') . "\n";
        Storage::disk('local')->delete("public/json/date/" . $date2->format('Y-m-d') . ".json");
//        $TheDayBefore=new DateTime('');
        $i = 0;
        $startTime = microtime(true);

        do {
            $data = self::getApiData("https://eapi.windrose.kiev.ua/windrose/website/schedule/?date=" . $date->format('Y-m-d'));


            Storage::disk('local')
                ->put("public/json/date/" . $date->format('Y-m-d') . ".json", \GuzzleHttp\json_encode($data));
            $i++;
            $date->add(new \DateInterval("P1D"));
            $that->info("$i data");
//        dd($data);
            if ($count)
                if ($count === $i) break;

            if ($data->status == "error") break;

        } while ($data->status == "success" && $data->code == "0");
        echo "Elapsed time is: " . (microtime(true) - $startTime) . " seconds";


    }

    static function getApiData($request)
    {
//       dd($method,$main,$request,$parametrs);
        $client = new Client();
        try {
            $response = $client->get($request);
            $body = $response->getBody();

            return \GuzzleHttp\json_decode($body);
        } catch (GuzzleException $exception) {
//           Log::error( $exception);
            var_dump($exception->getMessage());

            return (object)[
                "status" => "error",
                "code" => $exception->getCode()];
        }
    }
    protected function createArray($item, $object)
    {
        $object = collect($object);
        return (array)$object->get("$item");
    }

}
