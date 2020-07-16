<?php


namespace App\Telegram\keyboards;

use App\Chat;
use App\MenuItem;
use App\TelegramSetting;
use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Entities\Keyboard;
class GetMenuButtonMessage
{
    public $id;
    public function __construct($id)
    {
        $this->id=$id;
    }
     function fill_menu($main_menu_items,$chat_id){
        $count=$main_menu_items->count();
         $data=[];
         $itemsInData=[];
        $i=0;
         $chat = Chat::where('id',$chat_id)->first();

         $main_menu_items=  $main_menu_items->translate($chat->lang);
        foreach ($main_menu_items as $item){
            array_push($itemsInData, $item->title);
            $i++;
            if(count($itemsInData)==3){
                array_push($data, $itemsInData);
                $itemsInData=[];
            }
        }
        if ($count%3==2){
            array_push($data, [$main_menu_items->get($count-2)->title,$main_menu_items->get($count-1)->title]);
        }elseif ($count%3==1){
            array_push($data, $main_menu_items->get($count-1)->title);

        }else if($count==2){
            array_push($data, [$main_menu_items->get(0)->title,$main_menu_items->get(1)->title]);

        }else if($count==1){
            array_push($data, $main_menu_items->get(0)->title);

        }
        return $data;
    }

    function add_exit_button($all_items,$parent,$chat_id){
        $is_nested=false;
        foreach ($all_items as $item){
            if($item->parent_id==$this->id){
                $is_nested=true;
            }
        }
        $chat= Chat::where('id',$chat_id)->first();



        //добавляем кнопку выйти из подменю если оно вложенное
        $prefix=telegram_config('buttons.pref_back_menu',$chat->lang);
        if(!$prefix){
            $prefix='';
        }else{
            $prefix=$prefix.' ';
        }
        $postfix=telegram_config('buttons.post_back_menu',$chat->lang);;
        if(!$postfix){
            $postfix='';
        }else{
            $postfix=' '.$postfix;
        }
        if($is_nested){
            return $prefix.$parent->first()->title.$postfix;
        }

    }
    function get_submenu($chat_id){
        $limit =  setting('telegram.count_of_main_menu');
        $default_limit=9;
        if(!$limit){
            $limit=$default_limit;
        }
        $parent=MenuItem::
        where('menu_id','2')->
        where('id',$this->id)->get(); // вытягиваем родителя
        $all_items=MenuItem::
        where('menu_id','2')->get(); /// для проверки на родителя
        $main_menu_items=MenuItem::
            where('menu_id','2')
            ->where('parent_id',$this->id)
            ->orderBy('order','ASC')
            ->limit($limit)->get();
        $count=$main_menu_items->count();
        $chat= Chat::where('id',$chat_id)->first();
        $parent=  $parent->translate($chat->lang);
     $data= $this->fill_menu($main_menu_items,$chat_id);
     array_push($data, $this->add_exit_button($all_items,$parent,$chat_id));
        //проверка на вложенность
        //конец проверки на вложенность
        $keyboard = new Keyboard($data);
        $keyboard->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->setSelective(true);
        return $keyboard;
    }
    public function get_parentmenu($chat_id){
        $limit =  setting('telegram.count_of_main_menu');
        $default_limit=9;
        if(!$limit){
            $limit=$default_limit;
        }
        if($this->id){

            $all_items=MenuItem::
            orderBy('order','ASC')->
            where('menu_id','2')->get(); /// для проверки на родителя
            $parent=MenuItem::
            where('menu_id','2')->
            where('parent_id',$this->id)->
            orderBy('order','ASC')->
            get(); // вытягиваем родителя
            $solo_parent=   MenuItem::
            where('menu_id','2')->
            where('id',$this->id)->get(); // вытягиваем родителя



            $data=$this->fill_menu($parent,$chat_id);
            array_push($data, $this->add_exit_button($all_items,$solo_parent,$chat_id));
            $keyboard = new Keyboard($data);
            $keyboard->setResizeKeyboard(true)
                ->setOneTimeKeyboard(false)
                ->setSelective(true);

        }else{
            $main_menu=MenuItem::
            where('menu_id','2')->
            orderBy('order','ASC')->
            whereNull('parent_id')->get();
            $data=$this->fill_menu($main_menu,$chat_id);
            $keyboard = new Keyboard($data);
            $keyboard->setResizeKeyboard(true)
                ->setOneTimeKeyboard(false)
                ->setSelective(true);
        }
        return $keyboard;
    }

}
