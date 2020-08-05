<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use pimax\FbBotApp;
use pimax\Menu\LocalizedMenu;
use pimax\Menu\MenuItem;
use pimax\Messages\Message;

class FbMessangerController extends Controller
{


    public function hook(Request $request)
    {
//        dd($request);
        Log::alert(\GuzzleHttp\json_encode(file_get_contents('php://input')));
//dd("s");
        $access_token = env("FacebookBot_API");
        $verify_token = "test";
        $hub_verify_token = null;
        if (isset($_REQUEST['hub_challenge'])) {
            $challenge = $_REQUEST['hub_challenge'];
            $hub_verify_token = $_REQUEST['hub_verify_token'];
        }
        if ($hub_verify_token === $verify_token) {
            $myAccountItems[] = new MenuItem('postback', 'Pay Bill', 'PAYBILL_PAYLOAD');
            $historyItems[] = new MenuItem('postback', 'History Old', 'HISTORY_OLD_PAYLOAD');
            $historyItems[] = new MenuItem('postback', 'History New', 'HISTORY_NEW_PAYLOAD');
            $myAccountItems[] = new MenuItem('nested', 'History', $historyItems);
            $myAccountItems[] = new MenuItem('postback', 'Contact_Info', 'CONTACT_INFO_PAYLOAD');

            $myAccount = new MenuItem('nested', 'My Account', $myAccountItems);
            $promotions = new MenuItem('postback', 'Promotions', 'GET_PROMOTIONS_PAYLOAD');

            $enMenu = new LocalizedMenu('default', false, [
                $myAccount,
                $promotions
            ]);

            $arMenu = new LocalizedMenu('ar_ar', false, [
                $promotions
            ]);

            $localizedMenu[] = $enMenu;
            $localizedMenu[] = $arMenu;

//Create the FB bot
            $bot = new FbBotApp(env("FacebookBot_API"));
            $bot->deletePersistentMenu();
            $bot->setPersistentMenu($localizedMenu);
            $message = [];
            $message = [
                "messaging_type" => "text",
                "id" => "608992419753081",
                "text" => "hello"
            ];
            $recipient=["id"=>""];
            $bot->setGetStartedButton("START");

        }
    }

}
