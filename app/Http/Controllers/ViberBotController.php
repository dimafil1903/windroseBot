<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;
//use Paragraf\ViberBot\Bot;

//use Viber\Client;
//require_once("./vendor/autoload.php");

class ViberBotController
{

    public function index(Request $request){
//        (new Bot($request, new TextMessage()))
//            ->on(new MessageEvent($request->timestamp, $request->message_token,
//                new ViberUser($request->sender['id'],$request->sender['name']), $request->message))
//            ->hears("Hi!")
//            ->replay("Hello World!")
//            ->send();
    }
    public function set(){


        $apiKey = env("VIBERBOT_API"); // from "Edit Details" page
        $webhookUrl = env("APP_URL")."/viberhook"; // for exmaple https://my.com/bot.php

        try {
            $client = new Client([ 'token' => $apiKey ]);
            $result = $client->setWebhook($webhookUrl);
            echo "Success!\n";
        } catch (\Exception $e) {
//            echo "Error: ". $e->getError() ."\n";
        }
    }
}
