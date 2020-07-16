<?php
namespace Longman\TelegramBot\Commands\SystemCommands;

use App\LiqPay;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Payments;
class PayCommand extends UserCommand {
    protected $name = 'pay';
    protected $usage = '/pay';

    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $msg=$this->getMessage()->getText();
        $text    = 'Hi! Welcome to my bot!';

        $public_key = 'i2640596738';
        $private_key= '6CgrrgywK53TId9sqmuac1zE27K0DxtMcVCdPwyC';
        $liq= new LiqPay($public_key,$private_key);
        $res = $liq->api('request',array(

            'version'=>'3',

            'action'         => 'invoice_bot',
            'amount'         => '3', // сумма заказа
            'currency'       => 'UAH',

            'phone'  => '3809500070001',
            "order_id"=>'08778',
        ));
        $txt='err';
        foreach ($res as $val=>$key){
            if($val=='href'){
                $txt= $key;
            }

        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $txt,
        ];

         Request::sendMessage($data);
    }
}
