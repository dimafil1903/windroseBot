<?php


namespace App\Telegram\keyboards;


use App\Chat;
use App\DistributionType;
use App\MenuItem;
use App\TelegramDistribution;
use Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class MailInlineKeyboard
{


    public $chat_id;
    public $chat;
    public function __construct($chat_id)
    {
        $this->chat_id=$chat_id;
        $this->chat = Chat::where('id',  $this->chat_id)->first();
    }
    public function add_to_db(){

    }
    public function show_keyboard(){
        $distributions=DistributionType::all();
        $distributions=$distributions->translate($this->chat->lang);
        $array=[];
        foreach ($distributions as $distribution){
            array_push($array,[['text'=>$distribution->name, 'callback_data'=>'dist_'.$distribution->id]]);
        }
        $reply_keyboard= new InlineKeyboard($array);

      $text=  $this->getTitle('buttons.mailing');
             $data = [
                 'chat_id' => $this->chat_id,
                 'parse_mode'=>'HTML',
                 'text'=>$text,
                 'reply_markup' => $reply_keyboard
                 ];
             Request::sendMessage($data);

    }

    public function show_time_keyboard($id,$callback_message_id){
        $telegramDistribution = TelegramDistribution::where('type_id',$id)->where('chat_id',$this->chat_id)->first();


        $time_array=[];
        $three_arrays=[];
        $h=7;
        $text='Выберите время для ежедневной рассылки';
        $exist=false;
        for($i=0;$i<5;$i++){
            for($p=0;$p<3;$p++) {
                $s = mktime($h, 00);
                $s = date('H:i', $s);
                $h++;
                if($telegramDistribution) {
                    if ($telegramDistribution->time == $s) {
                        $exist=true;
                        $s = $s . " ✅";
                    }
                }
                array_push($three_arrays, ['text'=>$s, 'callback_data'=>'set_'.$id.'_'.$s]);

            }
            array_push($time_array,$three_arrays);
            $three_arrays=[];
        }
        if($exist){
            array_push($time_array,[['text'=>'Удалить рассылку', 'callback_data'=>'deldist_'.$id]]);
        }
        $timeKeyboard= new InlineKeyboard($time_array);

        $data = [
            'chat_id' => $this->chat_id,
            'parse_mode'=>'HTML',
            'message_id'=>$callback_message_id,
            'text'=>$text,
            'reply_markup' => $timeKeyboard
        ];
        if($telegramDistribution){
            Request::editMessageReplyMarkup($data);
            Request::editMessageText($data);
        }else{
            Request::editMessageReplyMarkup($data);
            Request::editMessageText($data);
        }
    }
    public function getTitle($button){
        $item= MenuItem::where('id',telegram_config_no_translate($button))->
        first();
        $chat = Chat::where('id',  $this->chat_id)->first();
        $item = $item->translate($chat->lang);

        return  $item['title'];
    }
}
