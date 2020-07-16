<?php

namespace App\Http\Controllers;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\StreamInterface;

class FlightsByDateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function index(Request $request)
    {

        if (!$request['date']) {
            return \response("Error date parameter", 403);
        }
        $current_page = 1;
        if ($request['page']) {
            $current_page = $request['page'];
        }
        $data = $this->getApiData("https://eapi.windrose.kiev.ua/windrose/website/schedule/?date=" . $request['date']);
        if (!$data) {
            return response($data, 404);
        }
        if ($data->status == "success") {
            /**
             * МАССИВ АЕРОПОРТОВ
             */
            $airports = collect($data->airports);
            /**
             * МАССИВ рейсов
             */
            $flights = collect($data->flights);
            $flightsCount = $flights->count();
            /**
             * МАССИВ самолетов
             */
            $fleets = collect($data->fleets);

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
            $lastPage = ceil($returnData->count() / 10);
            $All_INFO = new Collection();
            if ($current_page > $lastPage) {
                $current_page = $lastPage;
            }
            $All_INFO->put("last_page", $lastPage);
            $All_INFO->put("current_page", $current_page);
            $returnData = $returnData->forPage($current_page, 10);
            $All_INFO->put("data", $returnData);
            return response($All_INFO->toJson(), 200);
        } else {

            return response($data, 403);
        }


//        dd($data);

        return response("OK", 200);
    }

    /**
     * @param $string
     * @return void
     */
    public function getTime($string)
    {
        $format = 'Y-m-!d H:i:s';
        $date = DateTime::createFromFormat($format, $string);
        dd($date->format('H:i'));
    }

    /**
     * @param $item
     * @param $object
     * @return array
     */
    protected function createArray($item, $object)
    {
        $object = collect($object);
        return (array)$object->get("$item");
    }

    /**
     *
     * Get Api Data from any request
     * @param string $request request
     * @return bool
     */

    public function getApiData($request)
    {
//       dd($method,$main,$request,$parametrs);
        $client = new Client();
        try {
            $response = $client->get($request);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            return \GuzzleHttp\json_decode($body);
        } catch (GuzzleException $exception) {
//           Log::error( $exception);
            var_dump($exception->getMessage());
            return false;
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


}
