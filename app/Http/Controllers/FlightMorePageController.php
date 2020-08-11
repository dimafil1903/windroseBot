<?php


namespace App\Http\Controllers;


use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class FlightMorePageController extends Controller
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required',
            'number' => 'required',
            'lang' => 'required'
        ]);
        $flight = (new GetApi())->getOneFlight($request['date'], $request["number"]);
        $flightText = GetMessageFromData::generateCard($flight, $request['lang']);

     $list=   $this->getList($flight,$request['lang']);
        return view('flightPage', ['list' => $list]);

    }

    public function getList($flight, $lang)
    {

        $list=[];
        $langForApi = $lang;
        if ($langForApi == "uk") {
            $langForApi = "ua";
        }
        $from = (array)$flight["from"];
        $to = (array)$flight["to"];
        $fleet = (array)$flight["fleet"];
//        dd($flight);
        $list[] = "✈️ " . Lang::get("messages.flight_number", [], "$lang") . $flight['carrier'] . "-" . $flight["flight_number"];

        $list[] .= "\n🏙 "  . Lang::get("messages.from", [], "$lang") . $from["$langForApi"] . "  ";
        if (!empty($flight["from_terminal"])) {
            $list[] .= Lang::get("messages.from_terminal", [], "$lang") . $flight['from_terminal'];
        }
        $list[] .= "\n🌆 "  . Lang::get("messages.to", [], "$lang") . $to["$langForApi"] . "  ";
        if (!empty($flight["to_terminal"])) {
            $list[] .= Lang::get("messages.to_terminal", [], "$lang") . $flight['to_terminal'];
        }
        $list[] .= "\n📅 "  . Lang::get("messages.departure_date", [], "$lang") . date("d.m.Y", strtotime($flight['departure_date']));
        $list[] .= "\n🕐 "  . Lang::get("messages.departure_time", [], "$lang") . date("H:i", strtotime($flight['departure_date'])) . " " .
            Lang::get("messages.localTime", [], "$lang");
        $list[] .= "\n📆 "   . Lang::get("messages.arrival_date", [], "$lang") . date("d.m.Y", strtotime($flight['arrival_date']));
        $list[] .= "\n🕐 " . Lang::get("messages.arrival_time", [], "$lang") . date("H:i", strtotime($flight['arrival_date'])) . " " .
            Lang::get("messages.localTime", [], "$lang");;
        $list[] .= "\n⏳ " . Lang::get("messages.timeInFlight", [], "$lang") . FlightHelper::GetTimeInFlight($flight);

        $list[] .= "\n👀 " . Lang::get("messages.status", [], "$lang") . (FlightHelper::GetStatus($flight, "$lang"))->message;
//        dd((FlightHelper::GetStatus($flight, "$lang"))->message);
        return   $list;
    }

}
