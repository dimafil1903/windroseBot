<?php


namespace Longman\TelegramBot\Commands\SystemCommands;


use App\ShopOrder;
use App\TelegramCart;
use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Request;
class PreCheckoutQueryCommand extends SystemCommand
{
    protected $name = 'precheckoutquery';

    /**
     * @var string
     */
    protected $description = 'precheckoutquery';

    /**
     * @var string
     */
    protected $version = '1.1.1';
    public function execute(){
       $preCheckoutQuery= $this->getUpdate()->getPreCheckoutQuery();
        $id=$preCheckoutQuery->getId();
        $order=$preCheckoutQuery->getOrderInfo();
        $get_from_id=$preCheckoutQuery->getFrom()->getId();

         Request::answerPreCheckoutQuery([
            'pre_checkout_query_id' => $id,
            'ok'                    => true,
        ]);
         $this->add_to_db($get_from_id);
         $this->delete_from_cart($get_from_id);
    }

    public function add_to_db($get_from_id){
        $chat=DB::table('user_chat')->where('user_id',$get_from_id)->join('chats', function ($join) {
            $join->on('chats.id', '=', 'user_chat.chat_id')->where('chats.type','private');
        })->first();

        $order_items=TelegramCart::where('user_id',$chat->id)->where('status','added')->get();
        $date = date('Y-m-d H:i:s');
        foreach ($order_items as $order_item){
            ShopOrder::insert([
                'product_id' => $order_item->product_id,
                'client_id' => $order_item->user_id,
                'count'=>$order_item->count,
                'status'=>'success',
                'created_at'=>$date
            ]);
        }
    }
    public function delete_from_cart($get_from_id){
        $chat=DB::table('user_chat')->where('user_id',$get_from_id)->join('chats', function ($join) {
            $join->on('chats.id', '=', 'user_chat.chat_id')->where('chats.type','private');
        })->first();
        $order_items=TelegramCart::where('user_id',$chat->id)->where('status','added')->get();
        foreach ($order_items as $order_item){
            TelegramCart::where('product_id',$order_item->product_id)->
            where('user_id',$order_item->user_id)->delete();

        }

    }
}
