<?php
namespace Longman\TelegramBot\Commands\SystemCommands;

use App\MenuItem;
use App\Telegram\keyboards\InlineCategories;
use App\Telegram\keyboards\LangInlineKeyboard;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use App\Telegram\keyboards\MainKeyboard;
use App\Chat;
use Longman\TelegramBot\Conversation;
use App\TelegramSetting;

class StartCommand extends UserCommand {
    protected $name = 'start';
    protected $usage = '/start';
    protected $conversation;
    public function execute()
    {
        $message  = $this->getMessage();
        $keyboard = new MainKeyboard;

        $chat_id  = $message->getChat()->getId();
        $user    = $message->getFrom();
        $user_id = $user->getId();

        $text    = trim($message->getText(true));
    // Проверяем установленный ли язык (при первом запуске должен быть пустым)
        $default_lang='ru';// На случай если будет язык по умолчанию
       $chat= Chat::where('id',$chat_id)->first();





                if(!$chat->lang){
                    $lang_menu= new LangInlineKeyboard($chat_id);
                    $lang_menu->create_inline_menu();

                }else{

                    $settings = TelegramSetting::first();
                    $settings=  $settings->translate($chat->lang);
                    $hello_text = $settings->start_message;
                    $keyboard = $keyboard->getMainKeyboard($chat_id);
                    $data = [
                        'chat_id' => $chat_id,
                        'text'    => $hello_text,
                        'reply_markup' => $keyboard,
                    ];


                    Request::sendMessage($data);


                }





    }
}
