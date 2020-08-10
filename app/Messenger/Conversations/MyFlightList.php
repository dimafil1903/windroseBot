<?php


namespace App\Messenger\Conversations;


use App\FlightTracking;
use App\Messenger\DB;
use App\Messenger\keyboard\flightsKeyboard;
use App\MessengerUser;
use App\Telegram\Helpers\FlightHelper;
use App\Telegram\Helpers\GetApi;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use Illuminate\Support\Facades\Lang;

class MyFlightList extends Conversation
{


    private $page;

    public function myFlights($page)
    {

        $user = MessengerUser::where('user_id', "" . $this->getBot()->getUser()->getId())->first();
        $usersTracksFlights = FlightTracking::where("status", 1)
            ->where('type', 'messenger')
            ->where('chat_id', $user->user_id)->get();
        $this->page = $page;
//        (new FlightsKeyboard())->create($usersTracksFlights,$page,$user->lang);

        if ($usersTracksFlights->isEmpty()) {
            $q = Lang::get('messages.emptyFlightsList', [], $user->lang);
            $buttons[] = Button::create("Back")->value('main_menu');
            $this->ask(Question::create($q)->addButtons(
                $buttons
            ), function (Answer $answer) {
            });
        } else {
            $this->say(GenericTemplate::create()
                ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
                ->addElements((new FlightsKeyboard())->create($usersTracksFlights, $this->page, $user, null, true))
            );
            $last_page = 1;

            $last_page = (int)ceil((count($usersTracksFlights) / 10));

            $buttons = [];
            if ($last_page > 1) {
                for ($i = 1; $i <= $last_page; $i++) {



                    $button = Button::create($i)->value($i);
                    if ($this->page == $i) {
                        $button->image("https://img.pngio.com/check-mark-png-black-transparent-45018-free-icons-and-png-check-mark-no-background-png-1000_947.png");  }
                    $buttons[] = $button;
                }
            }
            $buttons[] = Button::create("Back")->value('main_menu');
            $this->ask(Question::create('Pick another page')->addButtons(
                $buttons

            ), function (Answer $answer) use ($usersTracksFlights, $user) {
                // Detect if button was clicked:
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
                    $this->myFlights($this->page);
                } else {
                    if ($answer->isInteractiveMessageReply()) {
                        $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                        $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'

                        $this->myFlights($selectedValue);

                    } else {
                        $this->myFlights($this->page);
                    }
                }
            });
        }
    }

    public function stopsConversation(IncomingMessage $message)
    {

        if ($message->getText() == 'main_menu') {
//            $this->getBot()->userStorage()->delete();
            return true;
        }

        return false;
    }

    public function run()
    {
        $this->myFlights(1);
    }
}
