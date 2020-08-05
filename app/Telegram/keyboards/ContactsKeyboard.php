<?php


namespace App\Telegram\keyboards;


use Illuminate\Support\Facades\Lang;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

class ContactsKeyboard
{

    public function getKeyboard($lang){
        $data=[];
           $button= new KeyboardButton(['text'=>Lang::get("messages.shareContact",[],$lang),'request_contact' => true]);
           $data[]=$button->setRequestContact(true);
        $keyboard = (new Keyboard($data));
        return $keyboard;
    }

}
