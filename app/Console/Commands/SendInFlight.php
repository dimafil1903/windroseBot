<?php

namespace App\Console\Commands;

use App\Chat;
use App\FlightTracking;
use App\Messenger\keyboard\MainKeyboard;
use App\MessengerUser;
use App\Viber\Keyboards\MainMenu;
use App\ViberUser;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\FacebookDriver;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Paragraf\ViberBot\Client;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class SendInFlight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:Flight';

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
        $currentTime1 = new DateTime('NOW');
        $currentTime2 = new DateTime('NOW');
        $currentTime3 = new DateTime('NOW');
        var_dump($currentTime1);
        $thirty = $currentTime1->add(new DateInterval("PT0H30M"));
        $flightsThirty = FlightTracking::

        where("expired_at_utc", "like", $thirty->format("Y-m-d H:i") . "%")
            ->get();

        $twelve = $currentTime2->add(new DateInterval("PT12H0M"));
        $flightsTwelve = FlightTracking::

        where("departure_date_utc", "like", $twelve->format("Y-m-d H:i") . "%")
            ->get();
        $oneDay = $currentTime3->add(new DateInterval("P1D"));
        $flightsOneDay = FlightTracking::

        where("departure_date_utc", "like", $oneDay->format("Y-m-d H:i") . "%")
            ->get();
        var_dump($currentTime3->format("Y-m-d H:i"));
//        var_dump($tracksToSendMessage,$currentTime->format("Y-m-d H:i"));
        $this->templateInFlight($flightsThirty, "messages.messageAboutThirtyMinutes");
        $this->templateInFlight($flightsTwelve, "messages.messageAboutTwelve");
        $this->templateInFlight($flightsOneDay, "messages.messageAboutOneDay");

    }

    public function templateInFlight($data, $message)
    {

        if ($data->isNotEmpty()) {
            foreach ($data as $item) {
                $from = (array)json_decode($item->fromJSON);
                $to = (array)json_decode($item->toJSON);
                $chat = Chat::find($item->chat_id);
                if ($item->type == "viber") {
                    $chat = ViberUser::where('user_id', "$item->chat_id")->first();
                } elseif ($item->type == "telegram") {
                    $chat = Chat::find($item->chat_id);
                } elseif ($item->type == 'messenger') {
                    $chat = MessengerUser::where('user_id', $item->chat_id)->first();
                }
                $lang = $chat->lang;
                $langApi = $lang;
                if ($lang == "uk") {
                    $langApi = "ua";
                }
                if ($item->status == 1) {
                    $text = " " . Lang::get($message, ["flight" => "$item->carrier-$item->flight_number " . $from["$langApi"] . "-" . $to["$langApi"], "timeDep" => "$item->departure_date", "time" => "$item->expired_at"], "$chat->lang");
                    if ($item->type == "telegram") {
//                        $chat = Chat::find($item->chat_id);
                        $this->info("$item->date");
                        $data = [
                            "chat_id" => $item->chat_id,
                            "text" => $text
                        ];
                        Request::sendMessage($data);
                    } elseif ($item->type == "viber") {

                        $keyboard = new MainMenu();
                        $keyboard = $keyboard->getKeyboard($lang);
                        $keyboard = $keyboard->getKeyboard();
                        (new Client())->broadcast($text, ViberUser::where('user_id', "$item->chat_id")->get(), $keyboard);
                    } elseif ($item->type == "messenger") {
                        $config = ['facebook' => [
                            'token' => env("FACEBOOK_TOKEN"),
                            'app_secret' => env("FACEBOOK_APP_SECRET"),
                            'verification' => env("FACEBOOK_VERIFICATION"),
                        ]
                        ];


                        $botman = BotManFactory::create($config, new LaravelCache());
                        try {
                            $botman->say(Question::create($text)->addButtons(

                                (new MainKeyboard())->getKeyboard($chat->lang)

                            ), $item->chat_id, FacebookDriver::class);
                        } catch (BotManException $e) {
                        }
                    }
                }
//                FlightTracking::destroy($item->id);


            }
//        $this->info("IM Start");
            Log::info("IM START log");
        } else {
            $this->info("EMPTY");
        }
    }
}
