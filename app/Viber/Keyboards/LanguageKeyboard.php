<?php


namespace App\Viber\Keyboards;


use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;

class LanguageKeyboard
{

    public function getKeyboard(){
        $buttons[] = (new Button("reply",
            "lang_uk"))
            ->setColumns(3)
            ->setRows(2)
            ->setImage('https://windrosebot.dimafilipenko.website/storage/photo/ukraine%20(1).png');
        $buttons[] = (new Button("reply",
            "lang_en"))
            ->setColumns(3)
            ->setRows(2)

            ->setImage("https://windrosebot.dimafilipenko.website/storage/photo/uk%20(2).png")
        ;
        $keyboard = new KeyboardMessage();

        return $keyboard->setKeyboard((new Keyboard($buttons))->setInputFieldState('hidden'));
    }
}
