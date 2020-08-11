<?php


namespace App\Messenger\Conversations;


use App\Messenger\DB;
use App\MessengerUser;
use App\Telegram\Helpers\FlightHelper;
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

use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Model\Keyboard;

class FlightsConversation extends Conversation
{

    public $date;
    public $format;
    public $answer;
    public $page;


    public function MakeList($page)
    {

//        Log::alert($this->bot->getMessage()->getText());
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
                    ->addElements((new FlightsKeyboard())->create($api, $this->page, $user, $this->date))
                );
                $last_page = 1;
                if (isset($api["last_page"])) {
                    $last_page = $api["last_page"];
                } else {
                    $last_page = (int)ceil((count($api) / 10));
                }
                $buttons = [];
                if ($last_page > 1) {
                    for ($i = 1; $i <= $last_page; $i++) {



                            $button = Button::create($i)->value($i);
                                if ($this->page == $i) {
                                    $button->image("https://img.pngio.com/check-mark-png-black-transparent-45018-free-icons-and-png-check-mark-no-background-png-1000_947.png");  }
                        $buttons[] = $button;
                    }
                }
                $buttons[] = Button::create(Lang::get('messages.back',[],$user->lang))->value('buttons.schedule');
                $this->ask(Question::create('Pick another page')->addButtons(
                    $buttons
                ), function (Answer $answer) use ($getApi, $api, $user) {

                    // Detect if button was clicked:
                    if ($answer->getMessage()) {
                        Log::alert($answer->getMessage()->getText());
                    }
                    $messageText = explode("_", $this->bot->getMessage()->getText());
                    if ($messageText[0] == "track") {
                        $date = $messageText[1];
                        $page = $messageText[2];
                        $flight_number = $messageText[3];
                        $status = $messageText[4];

                        $getApi = new GetApi();
                        $flight = $getApi->getOneFlight($date, $flight_number, $page);

//            dd($flight);
                        $code = FlightHelper::GetStatus($flight, $user->lang)->code;
                        if ($code == 2 && $code == 3) {
                            $answer = Lang::get("messages.thisFlightAlreadyArrived", [], $user->lang);
                            $this->say($answer);
                        } else {
                            if ($flight["delay"] != "0") {
                                $expired_at = date("Y-m-d H:i:s", strtotime($flight["arrival_date"]) + $flight['delay']);
                                $expired_at_utc = date("Y-m-d H:i:s", strtotime($flight["arrival_date_utc"]) + $flight['delay']);

                            } else {
                                $expired_at = $flight["arrival_date"];
                                $expired_at_utc = $flight["arrival_date_utc"];
                            }
                            (new DB())->createFlightTacking($date, $user, $flight, $page, $status, $expired_at, $expired_at_utc);

                            $from = (array)$flight["from"];
                            $to = (array)$flight["to"];
                            $lang = $user->lang;
                            if ($lang == "uk")
                                $lang = 'ua';
                            if ($status == 1) {


                                $answer = Lang::get("messages.answerForTrack", [], $user->lang) .
                                    "\n" . $flight["carrier"] . "-" . $flight_number . " " . $from[$lang] . "-" . $to[$lang];

                            } else {
                                $answer = Lang::get("messages.answerForTrackStop", [], $user->lang) .
                                    "\n" . $flight["carrier"] . "-" . $flight_number . " " . $from[$lang] . "-" . $to[$lang];

                            }
                            $this->say($answer);
                        }
                        $api = $getApi->getFlightsByDate($this->date);
                        $this->say(GenericTemplate::create()
                            ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
                            ->addElements((new FlightsKeyboard())->create($api, $this->page, $user, $this->date))
                        );
                        $this->repeat();
                    } else {
                        if ($answer->isInteractiveMessageReply()) {
                            $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                            $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'

//                        $this->stopsConversation($this->bot->getMessage());
//                        if ($selectedText)
//                        $this->say($selectedText);
                            $this->MakeList($selectedValue);

                        } else {
//                        $this->say("OOps");
//                        $this->stopsConversation($this->bot->getMessage());

                            $this->say(GenericTemplate::create()
                                ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
                                ->addElements((new FlightsKeyboard())->create($api, $this->page, $user, $this->date))
                            );
                            $this->repeat();
                        }
                    }
                });

            }
        }
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText()) {
            Log::warning($message->getText());
        }
        if ($message->getText() == 'buttons.schedule') {
//            $this->getBot()->userStorage()->delete();
            return true;
        }

        return false;
    }

    public function run()
    {
        $this->MakeList(1);
    }
}
