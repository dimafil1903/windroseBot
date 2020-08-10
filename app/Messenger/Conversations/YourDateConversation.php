<?php


namespace App\Messenger\Conversations;


use App\MessengerUser;
use App\Telegram\Helpers\ConvertDate;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class YourDateConversation extends Conversation
{


    public function yourDate()
    {
        $user = MessengerUser::where('user_id', "" . $this->getBot()->getUser()->getId())->first();
        $currentTime1 = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));
        $twoDay = $currentTime1->add(new DateInterval("P2D"));
        $answer = Lang::get("messages.inputYourDate", [], $user->lang) . "\n" .
            Lang::get("messages.example", ["date" => $twoDay->format("d.m")], $user->lang);

        $question = Question::create($answer)
            ->fallback('ОЙ')
            ->callbackId('cancel')
            ->addButtons([
                Button::create("cancel")->value('buttons.schedule'),

            ]);

        $this->ask($question, function (Answer $answer) use ($user) {
            // Detect if button was clicked:

            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'
//                $answer->setText('cancel');
//                $this->stopsConversation($this->getBot()->getMessage());
            } else {
                $text = $answer->getText();
                $date_array = explode('.', $text);
                $dt = DateTime::createFromFormat("d.m", $text);
                $dtf = DateTime::createFromFormat("d.m", $text);
                $errorMessage = '';
//            Log::info(\GuzzleHttp\json_encode($dtf->format("d.m")));
                if (!($dt !== false && !array_sum($dt::getLastErrors()))) {
                    $errorMessage = Lang::get("messages.wrongDateInput", [], $user->lang);
                    $this->say($errorMessage);
                    $this->repeat();
                }
                if (!$errorMessage) {
                    if ($dtf->format("m") < date("m")) {

                        $dt->add(new DateInterval("P1Y"));
                    } elseif ($dtf->format("m") == date("m")) {
                        if ($dtf->format("d") < date("d") - 1) {

                            $dt->add(new DateInterval("P1Y"));
                        }
                    }
                    $date = $dt->format("Y-m-d");
                    $answer = Lang::get('messages.FlightListsOn', ['date' => ConvertDate::ConvertToWordMonth($date, $user->lang)], $user->lang);

                    $this->getBot()->userStorage()->save([
                        'date' => $date,
                        'answer'=>$answer
                    ]);
                    $this->bot->startConversation(new FlightsConversation());
                }
            }


//                $this->askForLocation('Please tell me your location.', function (Location $location) {
//                    // $location is a Location object with the latitude / longitude.
//                });


        });
    }
    public function stopsConversation(IncomingMessage $message)
    {
        Log::critical($message->getText());
        if ($message->getText() == 'cancel') {

            return true;
        }
        if ($message->getText() == 'buttons.schedule') {

            return true;
        }
        return false;
    }

    function run()
    {
        $this->yourDate();
    }
}
