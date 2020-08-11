<?php

namespace App\Console\Commands;

use App\Messenger\keyboard\MainKeyboard;
use App\Viber\Keyboards\MainMenu;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Exceptions\Base\BotManException;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\FacebookDriver;
use Illuminate\Console\Command;
use Paragraf\ViberBot\Client;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;
use App\ViberUser;

use Longman\TelegramBot\Request;
use function GuzzleHttp\Promise\all;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle(PhpTelegramBotContract  $telegram_bot)
    {

        $this->send();
        dd('test');
    }
    public function send(){
        $data=[
            'chat_id'=>'481629579',

            'text'=>'test'
        ];

        Request::sendMessage($data);

//        ViberUser::all();
        $config = ['facebook' => [
            'token' => env("FACEBOOK_TOKEN"),
            'app_secret' => env("FACEBOOK_APP_SECRET"),
            'verification' => env("FACEBOOK_VERIFICATION"),
        ]
        ];
        $botman = BotManFactory::create($config, new LaravelCache());
        try {
            $botman->say(Question::create("TEST")->addButtons(

                (new MainKeyboard())->getKeyboard("uk")

            ),"3127568233946310",FacebookDriver::class);
        } catch (BotManException $e) {
        }

    }

}
