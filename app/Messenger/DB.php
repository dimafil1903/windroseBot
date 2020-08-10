<?php


namespace App\Messenger;


use App\FlightTracking;
use App\MessengerUser;
use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Interfaces\UserInterface;
use BotMan\BotMan\Users\User;
use Illuminate\Support\Facades\Lang;

class DB
{
    /**
     * @var BotManFactory
     */
    private $bot;

    /**
     * @var MessengerUser
     */
    /**
     * @param $user UserInterface
     * @return MessengerUser
     */
    public function insertUser($user)
    {

      return  MessengerUser::updateOrCreate([
            'user_id' => $user->getId()
        ], [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ]);

    }
    public function createFlightTacking($date,$user,$flight,$page,$status,$expired_at,$expired_at_utc)
    {

        $createFlightTracking = FlightTracking::updateOrCreate([
            "date" => $date,
            "type" => "messenger",
            "chat_id" => $user->user_id,
            "person_id" => $user->user_id,
            "flight_number" => $flight["flight_number"],
            'carrier' => $flight["carrier"],
        ],
            [
                "page" => "$page",
                "fromJSON" => \GuzzleHttp\json_encode($flight["from"]),
                "toJSON" => \GuzzleHttp\json_encode($flight["to"]),
                "status" => $status,
                "delay" => $flight["delay"],
                "expired_at" => $expired_at,
                "expired_at_utc" => $expired_at_utc,
                "departure_date" => $flight["departure_date"],
                "arrival_date" => $flight["arrival_date"],
                "departure_date_utc" => $flight["departure_date_utc"],
                "arrival_date_utc" => $flight["arrival_date_utc"],
            ]);

    }
    public function insertData()
    {

    }
}
