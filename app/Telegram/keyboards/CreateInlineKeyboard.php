<?php


namespace App\Telegram\keyboards;


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
    public function createWithCallback($data,$callbackPrefix,$callbackBody){
        dd($data);
        try {
            $inline_keyboard = new InlineKeyboard([[
                ['text' => '🇷🇺  ', 'callback_data' => 'lang_ru'],
                ['text' => '🇺🇦', 'callback_data' => 'lang_uk'],
                ['text' => '🇬🇧', 'callback_data' => 'lang_en'],
            ]]);
        } catch (TelegramException $e) {
            Log::error('Something is really going wrong.');

        }
        return $inline_keyboard;
    }
}
