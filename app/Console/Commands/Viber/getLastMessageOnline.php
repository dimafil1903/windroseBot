<?php

namespace App\Console\Commands\Viber;

use App\Chat;
use App\FlightTracking;
use App\LastMessage;
use App\Telegram\Helpers\GetApi;
use App\User;
use App\Viber\Keyboards\MainMenu;
use App\ViberUser;
use DateInterval;
use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;
use function Matrix\add;

class getLastMessageOnline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'viber:getOnline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get online users to redirect them to main menu  ';

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
    public function handle()
    {
        $client = new \Paragraf\ViberBot\Client();
        $lastMessages = LastMessage::where('checked', 0)->get();
        $dic = ["BackToSchedule", "Exit", "Вихід"];
        $users = new Collection();
        $p=0;
        foreach ($lastMessages as $lastMessage) {

            for ($i = 0; $i < count($dic); $i++) {
                if (strripos($lastMessage->text, $dic[$i]) === false) {
//                    echo "К сожалению, $dic[$i] не найдена в ($lastMessage->text)";
                    $users->put('id',$lastMessage->user_id)->unique('id');
                }

            }
            if ($p==100){
                break;
            }
            $p++;

        }
        $array=[];

        foreach ($users as $user){
            $array[]=$user;
        }
   $result= $client->getOnlineStatus($array);
        $keyboard= new MainMenu();

        if (isset($result->users)){
           $users= $result->users;
           foreach ($users as $user){


               if ($user->online_status!=0 ){
                   $lastMessage=LastMessage::where('user_id', $user->id)->first();
                   $date = new DateTime($lastMessage->updated_at);

                   $date->add(new DateInterval("PT120S"));
//
                   if ($date < new DateTime()) {
//                       dd($date->format("H:i:s"),(new DateTime())->format("H:i:s"));
                       $lang=(ViberUser::where("user_id", $user->id)->first())->lang;
                       $keyboard = $keyboard->getKeyboard($lang);
                       $keyboard = $keyboard->getKeyboard();
                       $answer = Lang::get('messages.returnToMainMenu',[],$lang);

                       (new \Paragraf\ViberBot\Client())->broadcast($answer, ViberUser::where('user_id', "$user->id")->get(), $keyboard);
                       LastMessage::where('user_id', $user->id)->delete();
                   }
               }
           }
        }
    }

}
