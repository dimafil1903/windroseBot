<?php

namespace App\Console\Commands;

use App\Chat;
use App\FlightTracking;
use App\Telegram\Helpers\GetApi;
use App\User;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class SendAndDeleteArrived extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:sendArrived';

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
        $currentTime = new DateTime('NOW');
        $tracksToDelete = FlightTracking::
//            ->where("delay","0")
        where("expired_at_utc", "<=", $currentTime->format("Y-m-d H:i:s"))
            ->get();
//        var_dump($tracksToDelete);
        $this->info($currentTime->format("Y-m-d H:i:s"));
        if ($tracksToDelete->isNotEmpty()) {
            foreach ($tracksToDelete as $item) {
//                $flight = GetApi::getOneFlight($item->date, $item->flight_number, $item->page);

//                var_dump($item->from);
                $from=(array) json_decode($item->fromJSON);
//                var_dump($from);
                $to=(array) json_decode($item->toJSON);
                $chat=Chat::find($item->chat_id);
                $lang=$chat->lang;
                $langApi=$lang;
                if ($lang=="uk"){
                    $langApi="ua";
                }
                if ($item->status == 1) {
                    $chat = Chat::find($item->chat_id);

                    $this->info("$item->date");
                    $data = [
                        "chat_id" => $item->chat_id,
                        "text" =>   " " . Lang::get("messages.messageAboutArrived", ["flight"=>"$item->carrier-$item->flight_number ". $from["$langApi"]."-".$to["$langApi"],"time"=>"$item->expired_at"], "$chat->lang")
                    ];
                    Request::sendMessage($data);
                }
                FlightTracking::destroy($item->id);


            }
//        $this->info("IM Start");
            Log::info("IM START log");
        }
    }
}
