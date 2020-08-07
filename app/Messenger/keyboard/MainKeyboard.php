<?php


namespace App\Messenger\keyboard;


use App\TelegramConfig;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use TCG\Voyager\Models\MenuItem;

class MainKeyboard
{
    public function getKeyboard($lang)
    {
        $main_menu = MenuItem::


            where('menu_id', '=', '2')
            ->whereNull('parent_id')
            ->orderBy('order', 'ASC')
            ->get();


//            $chat = Chat::where('id',$chat_id)->first();

        $main_menu = $main_menu->translate($lang);



        $buttons = [];
        foreach ($main_menu as $menu) {
            $key=  TelegramConfig::where('value',$menu->id)->first();
            $buttons[] = Button::create($menu->title)->value($key->key);
        }
        return $buttons;
    }

    public function getSub($id, $lang)
    {
        $main_menu = MenuItem::
        where('menu_id', '=', '2')

            ->where('parent_id', $id)
            ->orderBy('order', 'ASC')
            ->get();
        $main_menu = $main_menu->translate($lang);
        $buttons = [];
        foreach ($main_menu as $menu) {
            $key=  TelegramConfig::where('value',$menu->id)->first();
            $buttons[] = Button::create($menu->title)->value($key->key);
        }
        return $buttons;
    }
}
