<?php


namespace App\Viber;


use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Bot;
use Paragraf\ViberBot\Event\MessageEvent;
use Paragraf\ViberBot\Messages\KeyboardMessage;
use Paragraf\ViberBot\Model\Button;
use Paragraf\ViberBot\Model\Keyboard;
use Paragraf\ViberBot\Model\ViberUser;
use App\Viber\Commands;
class ViberBot
{

    public $request;
    private $event;
    private $sender;
    private $message;

    public function __construct($request)
    {

        if (!empty($request)) {
            $this->request = $request;
        }
        if (isset($request->event)) {
            $this->event = $request->event;
        }
        if (isset($request->sender)) {
            $this->sender = $request->sender;
        }
        if (isset($request->message)) {
            $this->message = $request->message;
        }
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function handle()
    {
//        Log::alert(($this->event));
        $this->execute();

    }

    public function execute(){
//        Commands\MessageCommand::
//        $event=$this->getEvent();
        $db=new DB($this->getRequest());
        $db->insertData();
      $event=  explode("_",$this->getEvent());
      $className="";
        foreach ($event as $str){
            $className.=ucfirst($str);
        }
       $name=  "App\Viber\Commands\\".$className."Command";
       $object= new $name($this->request);
           if (method_exists($object,"execute")) {
               $object->execute();
               Log::debug(\GuzzleHttp\json_encode($event));
           }else{
               Log::error("$name NO Command ");
           }
    }
}
