<?php


namespace App\Viber\Commands;


use App\Viber\Keyboards\LanguageKeyboard;
use App\Viber\Keyboards\MainMenu;

use App\Viber\ViberBot;
use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Bot;
use Paragraf\ViberBot\Event\ConversationStartedEvent;
use Paragraf\ViberBot\Event\MessageEvent;
use Paragraf\ViberBot\Model\ViberUser;

class ConversationStartedCommand extends ViberBot
{

    public function execute()
    {
        $user = new ViberUser($this->getRequest()->user['id'], $this->getRequest()->user['name']);

        Log::info(\GuzzleHttp\json_encode($this->getRequest()->message_token));

        $chat=\App\ViberUser::where("user_id",$user->getId())->first();
        if (!$chat->lang){
            $keyboard = new LanguageKeyboard();
            $keyboard = $keyboard->getKeyboard();
        }else{
            $keyboard = new MainMenu();
            $keyboard = $keyboard->getKeyboard($chat->lang);
        }
        $bot = new Bot($this->getRequest(), $keyboard);

        $bot->on(new ConversationStartedEvent($this->getRequest()->timestamp, $this->getRequest()->message_token,
            $user, "open","",false))
            ->replay(
               "HELLO ITS START CONVERSATION"
            )
            ->send();
    }
}
