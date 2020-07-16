<?php


namespace App\Http\Controllers;

use App\LiqPay;
class LiqPayController extends Controller
{
    public $href;

    function getForm(){




        $public_key = 'i2640596738';
        $private_key= '6CgrrgywK53TId9sqmuac1zE27K0DxtMcVCdPwyC';
        $json_string =' {"public_key":"'.$public_key.'","version":"3","action":"pay","amount":"3","currency":"UAH","description":"test","order_id":"403040042301"}';

        $data= base64_encode($json_string);
        $sign_string=$private_key.$data.$private_key;
        $signature=   base64_encode(sha1($sign_string));
        $url = " https://www.liqpay.ua/api/request" ;

// Initialize a CURL session.
            $liq= new LiqPay($public_key,$private_key);
           $res = $liq->api('request',array(

                'version'=>'3',

                'action'         => 'invoice_bot',
                'amount'         => '3', // сумма заказа
                'currency'       => 'UAH',

               'phone'  => '38095060070001',
                "order_id"=>"546e577",
            ));
           echo'<pre>';
           foreach ($res as $val=>$key){
               if($val=='href'){
                   echo $key;
               }

           }

            echo "</pre>";
return view('pay',['data' => $data,'signature'=>$signature]);
    }

}
