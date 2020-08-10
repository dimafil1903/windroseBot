<?php

namespace App\Http\Controllers;


use App\Messenger\Messenger;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Facebook\FacebookDriver;
use BotMan\BotMan\Cache\LaravelCache;
use Illuminate\Support\Facades\Log;


class FbMessengerController extends Controller
{
    public function hook()
    {


        $config = ['facebook' => [
            'token' => env("FACEBOOK_TOKEN"),
            'app_secret' => env("FACEBOOK_APP_SECRET"),
            'verification' => env("FACEBOOK_VERIFICATION"),
        ]
        ];
        Log::debug(\GuzzleHttp\json_encode(file_get_contents('php://input')));
        DriverManager::loadDriver(FacebookDriver::class);
        $botman = BotManFactory::create($config, new LaravelCache());
        (new Messenger())->handle($botman);

    }

}
