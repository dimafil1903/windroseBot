<?php

namespace App\Console\Commands;

use App\Viber\Keyboards\MainMenu;
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
        $keyboard= new MainMenu();
        $keyboard= $keyboard->getKeyboard('uk')->setType('keyboard');
        $keyboard= $keyboard->getKeyboard();
        $keyboard= $keyboard->setInputFieldState('hidden');
        $users=ViberUser::where('user_id',["RnxqjHvB2FJkT3XFO9aXWw=="])->get();
        (new Client())->broadcast('Hello',$users->toArray() ,$keyboard);

    }

}
