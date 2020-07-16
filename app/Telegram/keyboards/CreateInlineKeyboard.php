<?php


namespace App\Telegram\keyboards;


use App\Chat;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;

class CreateInlineKeyboard
{
    private $chat_id;

    public function  __construct($chat_id)
    {
        $this->chat_id=$chat_id;

    }
    public function createFlightsList($data){
        $chat=Chat::find($this->chat_id);
        $array=[];
        foreach ($data->data as $datum){
           $from= (array) $datum->from;
           $to= (array) $datum->to;
           $lang="en";
           if($chat->lang=="uk"){
               $lang="ua";
           }
            array_push($array,
                [['text' =>
                    $datum->carrier."-".
                    $datum->flight_number." ".
                    $from[$lang]."-".
                    $to[$lang],
                    'callback_data' => 'flight_number']]);
        }
        $miniArray=[
            ['text' => "<=", 'callback_data' => 'left'],
            ['text' => "=>", 'callback_data' => 'right']
        ];
        array_push($array,$miniArray);
//        dd($data->data);
        try {
            $inline_keyboard = new InlineKeyboard($array);
        } catch (TelegramException $e) {
            Log::error('Something is really going wrong.');

        }
        return $inline_keyboard;
    }
}
