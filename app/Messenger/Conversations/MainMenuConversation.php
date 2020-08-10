<?php


namespace App\Messenger\Conversations;




use App\Messenger\keyboard\MainKeyboard;
use App\MessengerUser;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Facades\Lang;

class MainMenuConversation extends Conversation
{

    public function mainMenu(){

        $user = MessengerUser::where('user_id', "" . $this->bot->getUser()->getId())->first();

        $this->getBot()->reply(Question::create(Lang::get('messages.mainMenu',
            [
                'name' => $user->first_name,
                "nameBot" => env("PHP_TELEGRAM_BOT_NAME")
            ],
            $user->lang))->addButtons(

            (new MainKeyboard())->getKeyboard($user->lang)

        ));
        $this->getBot()->removeStoredConversation();
    }

    public function run()
    {
        $this->mainMenu();
    }
}
