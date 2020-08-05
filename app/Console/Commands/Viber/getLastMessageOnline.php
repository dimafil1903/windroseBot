<?php

namespace App\Console\Commands\Viber;

use App\LastMessage;
use App\Viber\Keyboards\MainMenu;
use App\ViberUser;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

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
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $client = new \Paragraf\ViberBot\Client();
        $lastMessages = LastMessage::where('checked', 0)->get();
        $dic = ["BackToSchedule", "Exit", "Вихід", "lang"];
        $users = new Collection();
        $p = 0;
        foreach ($lastMessages as $lastMessage) {

            for ($i = 0; $i < count($dic); $i++) {
                if (strripos($lastMessage->text, $dic[$i]) === false) {
//                    echo "К сожалению, $dic[$i] не найдена в ($lastMessage->text)";
                    $users->put('id', $lastMessage->user_id)->unique('id');
                }

            }
            if ($p == 100) {
                break;
            }
            $p++;

        }
        $array = [];

        foreach ($users as $user) {
            $array[] = $user;
        }
        $result = $client->getOnlineStatus($array);
        $keyboard = new MainMenu();

        if (isset($result->users)) {
            $users = $result->users;
            foreach ($users as $user) {


                if ($user->online_status != 0) {
                    $lastMessage = LastMessage::where('user_id', $user->id)->first();
                    $date = new DateTime($lastMessage->updated_at);

                    $date->add(new DateInterval("PT300S"));
//
                    if ($date < new DateTime()) {
//                       dd($date->format("H:i:s"),(new DateTime())->format("H:i:s"));
                        $lang = (ViberUser::where("user_id", $user->id)->first())->lang;
                        $keyboard = $keyboard->getKeyboard($lang);
                        $keyboard = $keyboard->getKeyboard();
                        $answer = Lang::get('messages.returnToMainMenu', [], $lang);

                        (new \Paragraf\ViberBot\Client())->broadcast($answer, ViberUser::where('user_id', "$user->id")->get(), $keyboard);
                        LastMessage::where('user_id', $user->id)->delete();
                    }
                }
            }
        }
    }

}
