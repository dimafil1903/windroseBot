<?php


namespace App\Messenger\Conversations;


use App\MessengerUser;
use App\Telegram\Helpers\GetApi;
use App\Messenger\keyboard\flightsKeyboard;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\Element;
use BotMan\Drivers\Facebook\Extensions\ElementButton;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use Illuminate\Support\Facades\Lang;

use Paragraf\ViberBot\Model\Keyboard;

class FlightsConversation extends Conversation
{

    public $date;
    public $format;
    public $answer;
    public $page;


    public function MakeList($page)
    {


        $user = MessengerUser::where('user_id', "" . $this->getBot()->getUser()->getId())->first();

        $this->page = $page;

        $this->date = $this->getBot()->userStorage()->get('date');
        $this->format = $this->getBot()->userStorage()->get('format');
        $this->answer = $this->getBot()->userStorage()->get('answer');


        if ($this->date) {

            $getApi = new GetApi();
            $api = $getApi->getFlightsByDate($this->date);
//            dd($date);
//            dd($api);

            if (isset($api->code)) {
                if ($api->code == 404) {

                    $answer = Lang::get("messages.NoFlightsOnThisDate", [], $user->lang);

                }
                if ($api->code == 500) {

                    $answer = Lang::get("messages.serverError", [], $user->lang);

                }
            }
            if ($api) {
                $this->say(GenericTemplate::create()
                    ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
                    ->addElements((new FlightsKeyboard())->create($api, $this->page, $user->lang))
                );
                $last_page = 1;
                if (isset($api["last_page"])) {
                    $last_page = $api["last_page"];
                } else {
                    $last_page = (int)ceil((count($api) / 10));
                }
                $buttons = [];
                for ($i = 1; $i <= $last_page; $i++) {
                    $buttons[] = Button::create($i)->value($i);
                }
                $buttons[] = Button::create("Back")->value('stop');
                $this->ask(Question::create('Pick another page')->addButtons(
                    $buttons
                ), function (Answer $answer) {
                    // Detect if button was clicked:
                    if ($answer->isInteractiveMessageReply()) {
                        $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                        $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'
//                        $this->say($selectedText);

                        $this->MakeList($selectedValue);
//                $this->askForLocation('Please tell me your location.', function (Location $location) {
//                    // $location is a Location object with the latitude / longitude.
//                });
                    }
                });

            }
        }
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            $this->getBot()->userStorage()->delete();
            return true;
        }

        return false;
    }

    public function run()
    {
        $this->MakeList(1);
    }
}
