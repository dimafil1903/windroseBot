<?php


namespace App\Telegram\keyboards;
use App\Chat;
use App\TelegramSetting;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Exception\TelegramException;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Menu;
use Illuminate\Support\Facades\DB;
class MainKeyboard
{

    public function getMainKeyboard($chat_id){

        $limit =  setting('telegram.count_of_main_menu');
        $default_limit=9;
        if(!$limit){
            $limit=$default_limit;
        }
        $main_menu=MenuItem::
              where('menu_id','=','2')
            ->whereNull('parent_id')
            ->orderBy('order','ASC')
            ->limit($limit)
            ->get();


        $chat = Chat::where('id',$chat_id)->first();

        $main_menu=  $main_menu->translate($chat->lang);

        $count=$main_menu->count();
        $data=[];
        $itemsInData=[];
        $i=0;
        foreach ($main_menu as $item){
            array_push($itemsInData, $item->title);
            $i++;
                if(count($itemsInData)==2){
                    array_push($data, $itemsInData);
                    $itemsInData=[];
                }
            }
            if ($main_menu->count()%2==2){
                array_push($data, [$main_menu->get($main_menu->count()-2)->title,$main_menu->get($main_menu->count()-1)->title]);
            }elseif ($main_menu->count()%2==1){
                array_push($data, $main_menu->get($main_menu->count()-1)->title);
            } else if($count==2){
                 array_push($data, [$main_menu->get(0)->title,$main_menu->get(1)->title]);
            }else if($count==1){
                 array_push($data, $main_menu->get(0)->title);

             }
        try {
            $keyboard = new Keyboard($data);
        } catch (TelegramException $e) {
        }

        $keyboard->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->setSelective(true);
        return $keyboard;
    }
}
