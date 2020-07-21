<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;


use Longman\TelegramBot\Request;
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
    }

}
