<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\CheckedByUser;
use App\DistributionType;
use App\MenuItem;
use App\ShopCategory;
use App\ShopProduct;
use App\Telegram\keyboards\CartKeyboard;
use App\Telegram\keyboards\InlineCategories;
use App\Telegram\keyboards\InlineProduct;
use App\Telegram\keyboards\LangInlineKeyboard;
use App\Telegram\keyboards\MailInlineKeyboard;
use App\Telegram\keyboards\NewProductsInlineKeyboard;
use App\Telegram\keyboards\OrderKeyboard;
use App\TelegramDistribution;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use App\Chat;
use App\TelegramSetting;
use TCG\Voyager\Models\Setting;
use App\TelegramCart;
/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {

        $callback_query    = $this->getCallbackQuery();
        $chat_id    = $this->getCallbackQuery()->getMessage()->getChat()->id;
        $callback_query_id = $callback_query->getId();
        $callback_data     = $callback_query->getData();
        $callback_message_id     = $callback_query->getMessage()->getMessageId();


        /**
         *
         * ВАЖНЫЕ ПЕРЕМЕННЫЕ
         */

        $callbackPiece = explode("_", $callback_data);
 //       $answer_text=$callback_data;
        $answer_text=null;
        $chat=Chat::where('id',$chat_id)->first();

        /**
         * Манипуляции с выбором языком
         *
         */
        if($callbackPiece[0]=='lang'){
            $lang=$callbackPiece[1];
            $lang_menu = new LangInlineKeyboard($chat_id);
            $lang_menu->set($lang,$callback_message_id);
        }







        /**
         * Ловим id Рассылки для клиента и возвращаем клавиатуру с выбором времени
         */
        if($callbackPiece[0]=='dist'){
                $mailInlineKeyboard= new MailInlineKeyboard($chat_id);
                $mailInlineKeyboard->show_time_keyboard($callbackPiece[1],$callback_message_id);
        }
        if($callbackPiece[0]=='set'){
         TelegramDistribution::updateOrInsert(
             ['type_id'=>$callbackPiece[1],'chat_id'=>$chat_id],
             ['time' => $callbackPiece[2]]
         );
         $dist=DistributionType::where('id',$callbackPiece[1])->get();
         $dist=$dist->translate($chat->lang);
            $answer_text='Рассылка '.$dist[0]->name.' Установлена на '.$callbackPiece[2];
            Request::deleteMessage(['chat_id'=>$chat_id,'message_id'=>$callback_message_id]);
            Request::SendMessage(['chat_id'=>$chat_id,'text'=>$answer_text]);
        }
        if($callbackPiece[0]=='deldist'){
            TelegramDistribution::where('type_id',$callbackPiece[1])->where('chat_id',$chat_id)->delete();
            $data=['chat_id'=>$chat_id,'message_id'=>$callback_message_id];
Request::deleteMessage($data);
        }
        if($answer_text){
              $data = [
                    'callback_query_id' => $callback_query_id,
                    'text'              => $answer_text,
                    'show_alert'        => $callback_data === 'thumb up',
                    'cache_time'        => 5,
                ];

                 Request::answerCallbackQuery($data);
        }

    }
}
