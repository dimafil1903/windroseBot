<?php


namespace App\Viber\Keyboards;


use Illuminate\Support\Facades\Lang;
use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;

class ContactKeyboard
{
    public function getKeyboard($lang){
        $buttons[] = (new Button("share-phone",
            "getphone",Lang::get('messages.shareContact',[],$lang)))
            ->setColumns(6)
            ->setRows(2)
            ->setSilent(true);

        $keyboard = new KeyboardMessage();

        return $keyboard->setKeyboard((new Keyboard($buttons))->setInputFieldState('hidden'));
    }
}
