<?php


namespace App\Messenger;


use App\Chat;
use App\Messenger\Conversations\FlightsConversation;
use App\Messenger\Conversations\LangConversation;
use App\Messenger\Conversations\MainMenuConversation;
use App\Messenger\Conversations\MyFlightList;
use App\Messenger\Conversations\StartConversation;
use App\Messenger\Conversations\YourDateConversation;
use App\Messenger\keyboard\MainKeyboard;
use App\MessengerUser;
use App\Telegram\Helpers\ConvertDate;
use App\Telegram\Helpers\GetApi;
use App\TelegramConfig;
use App\Viber\Keyboards\FlightsKeyboard;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\Element;
use BotMan\Drivers\Facebook\Extensions\ElementButton;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Model\Keyboard;
use TCG\Voyager\Models\MenuItem;

class Messenger
{

    /**
     * @param $botman BotMan
     */
    public function handle($botman)
    {


        $botman->hears('start', function ($bot) {
            Log::debug(\GuzzleHttp\json_encode($bot->getUser()->getInfo()));
            $bot->startConversation(new StartConversation());
        });

        $main_menu_collection = TelegramConfig::all();
        $mainMenuArray = [];

        foreach ($main_menu_collection as $menu) {
            $mainMenuArray[] = $menu->key;
        }


//        $main_menu=$main_menu->translate();
//        $main_menu= $main_menu->toArray();
        $botman->hears($mainMenuArray, function (BotMan $bot) use ($main_menu_collection) {
            if (!$bot->getMessage()->isFromBot()) {
//                $payload = $bot->getMessage()->getPayload();
                $user = MessengerUser::where('user_id', "" . $bot->getUser()->getId())->first();


                if ($user) {
                    $text = $bot->getMessage()->getText();
                    $item = null;

                    $button = null;
                    if (isset($text)) {
                        $button = TelegramConfig::where('key', $text)->first();
                    }


                    if ($button) {
                        $hasChildrens = MenuItem::where('menu_id', 2)->where('parent_id', $button->value)->first();


                        if ($hasChildrens) {
                            $bot->reply(Question::create($this->getTitle($button->key, $user->lang))->addButtons(
                                (new MainKeyboard())->getSub($button->value, $user->lang)
                            ));
                        }

                    }
                }
            }
        });
        $botman->hears('buttons.change_lang', function ($bot) {

            $bot->startConversation(new LangConversation());
        });

        /**
         * date format
         */
        $format = "Y-m-d";
        /**
         * default value of date
         */
        $date = false;
        $yourDate = false;
        /**
         * default number of page
         */


        $page = 1;
        $fieldStatus = "hidden";

        $answer = "";
        $botman->hears('buttons.today', function ($bot) use ($format, $answer, $date) {

            $user = MessengerUser::where('user_id', "" . $bot->getUser()->getId())->first();

            $date = new DateTime('NOW', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
            $answer = Lang::get('messages.FlightListsOn', ['date' => ConvertDate::ConvertToWordMonth($date, $user->lang)], $user->lang);

            if ($date) {

                $bot->userStorage()->save([
                    'date' => $date,
                    'format' => $format,
                    'answer' => $answer
                ]);

                $bot->startConversation(new FlightsConversation());
            }

        });
        $botman->hears('buttons.tomorrow', function ($bot) use ($format, $answer, $date) {

            $user = MessengerUser::where('user_id', "" . $bot->getUser()->getId())->first();

            $date = new DateTime('tomorrow', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
            $answer = Lang::get('messages.FlightListsOn', ['date' => ConvertDate::ConvertToWordMonth($date, $user->lang)], $user->lang);

            if ($date) {

                $bot->userStorage()->save([
                    'date' => $date,
                    'format' => $format,
                    'answer' => $answer
                ]);

                $bot->startConversation(new FlightsConversation());
            }
        });
        $botman->hears('buttons.yesterday', function ($bot) use ($format, $answer, $date) {

            $user = MessengerUser::where('user_id', "" . $bot->getUser()->getId())->first();

            $date = new DateTime('yesterday', new DateTimeZone('Europe/Kiev'));

            $date = $date->format($format);
            $answer = Lang::get('messages.FlightListsOn', ['date' => ConvertDate::ConvertToWordMonth($date, $user->lang)], $user->lang);
            if ($date) {

                $bot->userStorage()->save([
                    'date' => $date,
                    'format' => $format,
                    'answer' => $answer
                ]);

                $bot->startConversation(new FlightsConversation());
            }
        });
//        "track_" . $date . '_' . $current_page . "_" . $datum["flight_number"] . "_" . $status

        $botman->hears('buttons.myFlightList', function (BotMan $bot) {
            $bot->startConversation(new MyFlightList());
        });
        $botman->hears('buttons.your_date', function ($bot) {
            $bot->startConversation(new YourDateConversation());
        });
        $botman->hears('main_menu', function ($bot) {
            $bot->startConversation(new MainMenuConversation());
        });

        $botman->fallback(function (BotMan $bot) {
            $userM = MessengerUser::where('user_id', "" . $bot->getUser()->getId())->first();

            if ($userM) {
                $bot->reply(
                    Question::create(Lang::get('messages.fallbackMessage',
                        [
                            'name' => $bot->getUser()->getFirstName(),
                            "nameBot" => env("PHP_TELEGRAM_BOT_NAME")
                        ],
                        $userM->lang))->addButtons(

                        (new MainKeyboard())->getKeyboard($userM->lang)

                    )
                );
            }
        });
// Start listening
        $botman->listen();
    }

    public function getTitle($button, $lang)
    {
        $item = \App\MenuItem::where('id', telegram_config_no_translate($button))->
        first();

        $item = $item->translate($lang);

        return $item['title'];
    }

}
