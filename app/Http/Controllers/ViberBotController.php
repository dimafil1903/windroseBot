<?php


namespace App\Http\Controllers;

use App\Viber\ViberBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Paragraf\ViberBot\Bot;
use Paragraf\ViberBot\Client;
use Paragraf\ViberBot\Event\Event;
use Paragraf\ViberBot\Event\MessageEvent;
use Paragraf\ViberBot\Http\Http;
use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Messages\Message;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;
use Paragraf\ViberBot\Model\ViberUser;
use Paragraf\ViberBot\TextMessage;
use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\ViberBotServiceProvider;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

//use Viber\Client;
//require_once("./vendor/autoload.php");

class ViberBotController extends Controller
{


    public function hook(Request $request)
    {
        $bot=new ViberBot($request);
        $bot->handle();

    }

    public function set(Request $request)
    {

//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://chatapi.viber.com/pa/set_webhook",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS =>"{ \"auth_token\": \"4bd3a6933a67dcd6-7712db8f473d1a50-795c4d19823dae25\", \"url\": \"https://www.windrosebot.dimafilipenko.website/hook\" }",
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: text/plain"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//        echo $response;

//        dd($response->getBody());
//dd($url);

//
//        $ch = curl_init($urlApi);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//        $result = curl_exec($ch);
//        dd(curl_exec($ch));
//        curl_close($ch);

//        Log::alert($request."\n".$result);

//        if (Http::call('POST', 'set_webhook', [
//            'url' => $url ? $url : route('viber-bot'),
//            'event_types' => config('viberbot.event_types'),
//            'send_name'=> true,
//            'send_photo'=> true,
//        ])) {
//            dd('Something went wrong!');
//        }
//
//        dd('You initialize successfully your route!');
    }

    public function unset(Request $request)
    {

//        $url = "";
//        $apiToken=env("VIBERBOT_API");
////dd($url);
//        $urlApi = 'https://chatapi.viber.com/pa/set_webhook';
//        $jsonData='{ "auth_token": "'.$apiToken.'", "url": "'.$url.'" }';
//        $ch = curl_init($urlApi);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//        $result = curl_exec($ch);
//
//        curl_close($ch);

//        Log::alert($request."\n".$result);

//        if (Http::call('POST', 'set_webhook', [
//            'url' => $url ? $url : route('viber-bot'),
//            'event_types' => config('viberbot.event_types'),
//            'send_name'=> true,
//            'send_photo'=> true,
//        ])) {
//            dd('Something went wrong!');
//        }
//
//        dd('You initialize successfully your route!');
    }

    public function getInfo()
    {
        $url = env("APP_URL") . "/vhook";
        $apiToken = env("VIBERBOT_API");

        $url = 'https://chatapi.viber.com/pa/get_account_info';
        $jsonData = '{ "auth_token": "' . $apiToken . '"}';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
