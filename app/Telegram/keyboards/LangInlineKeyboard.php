<?php


namespace App\Telegram\keyboards;


use App\Chat;
use App\TelegramSetting;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use App\Telegram\keyboards\MainKeyboard;
class LangInlineKeyboard
{

    public $callback_data;
    public $chat_id;
    public $callback_message_id;

    public function  __construct($chat_id)
    {
        $this->chat_id=$chat_id;

    }

    public function create_inline_menu(){
        try {
            $inline_keyboard = new InlineKeyboard([[
                ['text' => '🇷🇺  ', 'callback_data' => 'lang_ru'],
                ['text' => '🇺🇦', 'callback_data' => 'lang_uk'],
                ['text' => '🇬🇧', 'callback_data' => 'lang_en'],
            ]]);
        } catch (TelegramException $e) {
            Log::error('Something is really going wrong.');

        }
        $choose_lang_text='Выберите язык / Оберіть мову / choose language';
        $data = [
            'chat_id' => $this->chat_id,
            'text'    => $choose_lang_text,
            'reply_markup' => $inline_keyboard,

        ];

        try {
            Request::sendMessage($data);
        } catch (TelegramException $e) {
            Log::error('ERROR LANGUAGE KEYBOARD GENERATION');
        }
    }

    public function set($language,$callback_message_id){


        $settings = TelegramSetting::first();



                $settings=  $settings->translate($language);
                $change_lang_text = $settings->message_on_success_lang_change;
                Chat::where('id',$this->chat_id)->update(['lang' => $language]);
                $data=[
                    'chat_id'=>$this->chat_id,
                    'message_id'=>$callback_message_id,
                ];
                Request::deleteMessage($data);

                $keyboard = new MainKeyboard;
                $keyboard = $keyboard->getMainKeyboard($this->chat_id);
                $data = [
                    'chat_id' => $this->chat_id,
                    'text'    => $change_lang_text,
                    'reply_markup' => $keyboard,

                ];

                try {
                    Request::sendMessage($data);
                } catch (TelegramException $e) {
                }

        }


}
