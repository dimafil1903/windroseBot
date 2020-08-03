<?php


namespace App\Viber\Keyboards;


use App\Chat;

use App\FlightTracking;
use App\Telegram\keyboards\CreateInlineKeyboard;
use Illuminate\Support\Facades\Lang;
use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;
use TCG\Voyager\Models\MenuItem;

class MainMenu
{

    protected $chat_id;
    public function __construct($chat_id=null)
    {
        $this->chat_id=$chat_id;
    }

    public function getButtons($data,$parent=null){

            $buttons=[];
            $itemsInData=[];
            $i=0;
            $count=$data->count();
            $columns=6;
            if ($count%2==0){
                $columns=3;
            }else if($count%3==0){
                $columns=1;
            }


            foreach ($data as $item){
                $buttons[] = (new Button("reply",
                    "$item->title",
                    "<font color='#FFFFFF'>$item->title</font>",
                    "regular"))->setColumns(6)
                    ->setRows(1)

                    ->setSilent(true)
                ->setBgColor("#8176d6");

            }
            return $buttons;
        }
        public function getKeyboard($lang){
            $main_menu=MenuItem::
            where('menu_id','=','2')
                ->whereNull('parent_id')
                ->orderBy('order','ASC')
                ->get();


//            $chat = Chat::where('id',$chat_id)->first();

            $main_menu=  $main_menu->translate($lang);



            $buttons= $this->getButtons($main_menu);
            $keyboard = new KeyboardMessage();

            return $keyboard->setKeyboard((new Keyboard($buttons))->setInputFieldState('hidden'));
        }
    function exit_button( $parent,$lang)
    {
//        $is_nested = false;
//        foreach ($all_items as $item) {
//            if ($item->parent_id == $this->id) {
//                $is_nested = true;
//            }
//        }
//        $chat = Chat::where('id', $chat_id)->first();


        //добавляем кнопку выйти из подменю если оно вложенное
        $prefix = telegram_config('buttons.pref_back_menu', $lang);
        if (!$prefix) {
            $prefix = '';
        } else {
            $prefix = $prefix . ' ';
        }
        $postfix = telegram_config('buttons.post_back_menu', $lang);;
        if (!$postfix) {
            $postfix = '';
        } else {
            $postfix = ' ' . $postfix;
        }

            return $prefix . $parent->title . $postfix;


    }
        public function getSubMenu($id,$lang,$fieldState="hidden"){
            $menu_items = \App\MenuItem::
            where('menu_id', '2')
                ->where('parent_id', $id)
                ->orderBy('order', 'ASC')
                ->get();
            $parent = MenuItem::where('menu_id', '2')->where('id',$id)->first();
            $parent = $parent->translate($lang);
            $menu_items=$menu_items->translate($lang);
            $buttons= $this->getButtons($menu_items,$parent);
            $buttons[]=(new Button("reply",
                $this->exit_button($parent,$lang),
               "<font color='#FFFFFF'>".$this->exit_button($parent,$lang)."</font>" ,
                "regular"))
                ->setColumns(6)
                ->setRows(1)
                ->setSilent(true)
                ->setBgColor("#8176d6");


            return (new Keyboard($buttons))->setInputFieldState($fieldState)->setType("keyboard");


        }
}
