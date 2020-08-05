<?php

namespace App\Console\Commands;

use App\Chat;
use App\FlightTracking;
use App\Telegram\Helpers\GetApi;
use App\User;
use App\Viber\Keyboards\MainMenu;
use App\ViberUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Paragraf\ViberBot\Client;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class TrackingFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:changeDelay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tracking flight ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param PhpTelegramBotContract $telegram_bot
     * @return void
     * @throws TelegramException
     */
    public function handle(PhpTelegramBotContract $telegram_bot)
    {

        $tracksWithDelay = FlightTracking::where("status", 1)->get();
//       dd($tracksWithDelay);
        $getApi = new GetApi();
        foreach ($tracksWithDelay as $item) {

            $flight = $getApi->getOneFlight($item->date, $item->flight_number);
            $this->info(json_encode($flight));

                if ($item->delay !== $flight["delay"]) {


                    if (($item->type=="telegram") ) {
                        $chat = Chat::find($item->chat_id);
                    }elseif ($item->type=="viber"){
                        $chat = ViberUser::where('user_id',$item->chat_id)->first();
                    }
                    $this->info("$item->date");
                    $text=Lang::get("messages.messageAboutDelay", ["flight" => $item->carrier . "-" . $item->flight_number, "delay" => gmdate("H:i", (int)$flight["delay"])], "$chat->lang");

                    if ($item->type=="telegram") {
                        $data = [
                            "chat_id" => $item->chat_id,

                            "text" => $text
                        ];
                        Request::sendMessage($data);
                    }elseif ($item->type=="viber"){

                        $keyboard= new MainMenu();
                        $keyboard= $keyboard->getKeyboard("$chat->lang");
                        $keyboard= $keyboard->getKeyboard();
                        (new Client())->broadcast($text, ViberUser::where('user_id',"$item->chat_id")->get(),$keyboard);
                    }
                    $flightUpdate = FlightTracking::find($item->id);
                    $flightUpdate->delay = $flight["delay"];
                    $flightUpdate->delay_send++;
                    $flightUpdate->expired_at = date("Y-m-d H:i:s", strtotime($flightUpdate["expired_at"]) + $flight["delay"]);
                    $flightUpdate->expired_at_utc = date("Y-m-d H:i:s", strtotime($flightUpdate["expired_at_utc"]) + $flight["delay"]);
                    $flightUpdate->save();
                }

        }
//        $this->info("IM Start");
        $this->info("IM START log");
    }
}
