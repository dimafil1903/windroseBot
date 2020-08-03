<?php


namespace App\Viber\Keyboards;


use App\FlightTracking;
use App\Telegram\Helpers\FlightHelper;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;


class FlightKeyboard
{

    public function createCardButtons($flight, $page, $date, $chat_id, $lang, $type = "list", $FieldState = "hidden")
    {

        $flightInDB = FlightTracking::
        where("date", $date)
            ->where("type", "viber")
            ->where("chat_id", $chat_id)
            ->where("flight_number", $flight["flight_number"])
            ->first();
        $isEnabled = "";
        $track = Lang::get("messages.track", [], "$lang");
        if ($flightInDB) {
            if ($flightInDB->status == 0) {
                $status = 1;
            } else {
                $isEnabled = "❌";
                $track = Lang::get("messages.tracking", [], "$lang");
                $status = 0;
            }
        } else {
            $status = 1;
        }

        $buttons = [];
//            dd(FlightHelper::GetStatus($flight)->code);
        if (FlightHelper::GetStatus($flight, $lang)->code !== 2 && FlightHelper::GetStatus($flight, $lang)->code !== 3) {
            if ($type == "myList") {

                array_push($buttons, (
                new Button("", ""))
                    ->setText("<font color='#FFFFFF'>".$track . " $isEnabled"."</font>")
                    ->setSilent(true)
                    ->setActionBody("track_$date" . "_$page" . "_" . $flight["flight_number"] . "_$status" . "_myList")
                    ->setActionType('reply')
                    ->setBgColor("#8176d6")
                    ->setTextSize("large")
                );
            } elseif ($type == "list") {
                array_push($buttons, (new Button("", ""))
                    ->setText("<font color='#FFFFFF'>".$track . " $isEnabled"."</font>")
                    ->setSilent(true)
                    ->setActionBody("track_$date" . "_$page" . "_" . $flight["flight_number"] . "_$status")
                    ->setActionType('reply')
                    ->setBgColor("#8176d6")
                    ->setTextSize("large")
                );

            }
        }
//            array_push($keyboard, [['text' => Lang::get("messages.share", [], "$lang"), 'switch_inline_query' => $flight["carrier"] . "-" . $flight["flight_number"] . " $date"]]);
        if ($type == "list") {
//                array_push($keyboard, [['text' => Lang::get("messages.backToList", [], "$lang"), 'callback_data' => "backToFlightList_$date" . "_$page"]]);
            array_push($buttons, (new Button("", ""))
                ->setText("<font color='#FFFFFF'>".Lang::get("messages.backToList", [], $lang)."</font>")
                ->setSilent(true)
                ->setActionBody("backToFlightList_$date" . "_$page" . "_$FieldState")
                ->setActionType('reply')
                ->setBgColor("#8176d6")
                ->setTextSize("large")
            );
        } else if ($type == "myList") {
//                array_push($keyboard, [['text' => Lang::get("messages.backToList", [], "$lang"), 'callback_data' => "backToMyFlightList"]]);
            array_push($buttons, (new Button("", ""))
                ->setText("<font color='#FFFFFF'>".Lang::get("messages.backToList", [], $lang)."</font>")
                ->setSilent(true)
                ->setActionBody("BackToMyList"."_$page")
                ->setActionType('reply')
                ->setBgColor("#8176d6")
                ->setTextSize("large")
            );
        }

        return (new Keyboard($buttons))->setInputFieldState($FieldState);
    }


}
