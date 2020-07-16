<?php


namespace App\Http\Controllers;
use Longman\TelegramBot\Entities\Payments\PreCheckoutQuery;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;
use Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand;
use Longman\TelegramBot\Request as Telegram_Request;
use Illuminate\Http\Request as Request;
use Illuminate\Http\UploadedFile;
use App\Chat;
class SendMessage extends Controller
{


    public function index(){
       $chats= Chat::all();

        return view('admin.sendMessage',['chats'=>$chats]);
    }
    public function send(PhpTelegramBotContract $telegram_bot,Request $request ){

        $title = $request->input('title');
        $chat_id='';
        $body = $request->input('body');
        if($request->chat_id=='all'){
            $chats= Chat::all();
            if($request->file('photo')){
                $file = $request->file('photo')->store('telegram_uploads','public');
                $path =asset('/storage/'.$file);
                foreach ($chats as $chat){
                    $data=['chat_id'=>$chat->id,
                        'photo'   => Telegram_Request::encodeFile($path),
                        'caption'=>$title."\n\n".$body];
                    Telegram_Request::sendPhoto($data);
                }

            }else{
                foreach ($chats as $chat) {
                    $data = ['chat_id' => $chat->id, 'text'=>$title."\n\n".$body];
                    Telegram_Request::sendMessage($data);
                }
            }
        }else{
            $chat_id=$request->chat_id;
            if($request->file('photo')){
                $file = $request->file('photo')->store('telegram_uploads','public');
                $path =asset('/storage/'.$file);
                $data=[
                    'chat_id'=>$chat_id,
                    'photo'   => Telegram_Request::encodeFile($path),
                    'caption'=>$title."\n\n".$body
                ];
                Telegram_Request::sendPhoto($data);
            }else{
                $data=[
                    'chat_id'=>$chat_id,

                    'text'=>$title."\n\n".$body
                ];

                Telegram_Request::sendMessage($data);
            }
        }






return redirect()->back()->with('success','You have successfully upload image.');


    }
    public function check(){
        $data=[
            'chat_id'=>'481629579',
            'title'=>'title',
            'description'=>'description',
            'payload'=>'payload',
            'provider_token'=>'635983722:LIVE:i2466585764',
            'start_parameter'=>'start',
            'currency'=>'UAH',
            'prices'=>[

                ['label'=>'test label','amount'=>100]
            ],


        ];
        $t=new PreCheckoutQuery($data);
        $t->answer(true,$data);
        \Longman\TelegramBot\Request::answerPreCheckoutQuery($data);
    }
}
