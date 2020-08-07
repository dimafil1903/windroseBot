<?php


namespace App\Messenger\Conversations;


use App\Messenger\DB;
use App\Messenger\keyboard\MainKeyboard;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Facades\Lang;


class LangConversation extends Conversation
{





    public $user;

    public $firstTime=true;

    public function askLang()
    {
        $lang=$this->user->lang;
        $lang=null;
        $firstTime=!empty($lang);

        if ($lang){
            $this->sayStartMessage();
        }else {
            $question = Question::create('Оберіть мову/Choose your language')
                ->fallback('ОЙ')
                ->callbackId('lang')
                ->addButtons([
                    Button::create('Українська')->value('uk'),
                    Button::create('English')->value('en'),
                ]);


            $this->ask($question, function (Answer $answer) use ($firstTime) {
                // Detect if button was clicked:
                if ($answer->isInteractiveMessageReply()) {
                    $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                    $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'
                    $this->user->lang = $answer->getValue();
                    $this->user->save();
                    $this->say(Lang::get('messages.changedLang', [], $this->user->lang));

                    $this->sayStartMessage();

//                $this->askForLocation('Please tell me your location.', function (Location $location) {
//                    // $location is a Location object with the latitude / longitude.
//                });
                } else {
                    $this->repeat();
                }
            });
        }
    }

    public function sayStartMessage()
    {



        $this->getBot()->reply(Question::create(Lang::get('messages.startMessage',
            [
                'name' => $this->user->first_name,
                "nameBot" => env("PHP_TELEGRAM_BOT_NAME")
            ],
            $this->user->lang))->addButtons(

            (new MainKeyboard())->getKeyboard($this->user->lang)

        ));
        $this->getBot()->removeStoredConversation();


    }

    public function run()
    {
        $user = (new DB())->insertUser($this->getBot()->getUser());
        if ($user) {
//         Log::notice("USER IS ALIVE".json_encode($user));
            $this->user = $user;
        }

        $this->askLang();



//        $this->askEmail();
//        $this->getLang();

    }
}
