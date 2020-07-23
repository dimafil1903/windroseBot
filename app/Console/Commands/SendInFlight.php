<?php

namespace App\Console\Commands;

use App\Chat;
use App\FlightTracking;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class SendInFlight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:inFlight';

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
        $currentTime = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));
        $thirty = $currentTime->add(new DateInterval("PT0H30M"));
        $flightsThirty = FlightTracking::

        where("expired_at", "like", $thirty->format("Y-m-d H:i") . "%")
            ->get();

        $twelve = $currentTime->add(new DateInterval("PT12H0M"));
        $flightsTwelve = FlightTracking::

        where("departure_date", "like", $twelve->format("Y-m-d H:i") . "%")
            ->get();
        $oneDay = $currentTime->add(new DateInterval("P1D"));
        $flightsOneDay = FlightTracking::

        where("departure_date", "like", $oneDay->format("Y-m-d H:i") . "%")
            ->get();

//        var_dump($tracksToSendMessage,$currentTime->format("Y-m-d H:i"));
        $this->templateInFlight($flightsThirty, "messages.messageAboutThirtyMinutes");
        $this->templateInFlight($flightsTwelve, "messages.messageAboutTwelve");
        $this->templateInFlight($flightsOneDay, "messages.messageAboutOneDay");

    }

    public function templateInFlight($data, $message)
    {

//        $this->info($currentTime->format("Y-m-d H:i:s"));
        if ($data->isNotEmpty()) {
            foreach ($data as $item) {
//                $flight = GetApi::getOneFlight($item->date, $item->flight_number, $item->page);

//                var_dump($item->from);
                $from = (array)json_decode($item->fromJSON);
//                var_dump($from);
                $to = (array)json_decode($item->toJSON);
                $chat = Chat::find($item->chat_id);
                $lang = $chat->lang;
                $langApi = $lang;
                if ($lang == "uk") {
                    $langApi = "ua";
                }
                if ($item->status == 1) {
                    $chat = Chat::find($item->chat_id);

                    $this->info("$item->date");
                    $data = [
                        "chat_id" => $item->chat_id,
                        "text" => " " . Lang::get($message, ["flight" => "$item->carrier-$item->flight_number " . $from["$langApi"] . "-" . $to["$langApi"], "time" => "$item->expired_at"], "$chat->lang")
                    ];
                    Request::sendMessage($data);
                }
//                FlightTracking::destroy($item->id);


            }
//        $this->info("IM Start");
            Log::info("IM START log");
        }
    }
}
