<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Http\Request;
use BotMan\BotMan\Messages\Incoming\Answer;

class BotmanController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function index()
    {
        $botman = app('botman');

        $botman->hears('{message}', function (Botman $botman, $message) {

            if ($message == 'hi') {
                $this->askName($botman);
            } else {
                $botman->reply("write 'hi' for ...");
            }

            if ($message == 'start') {
                $botman->typesAndWaits(5);

                $botman->reply(Question::create('hello bro')->addButton(Button::create('test fsd sfd sfd sfd sfd sf')->value('test')));
            }
        });

        $botman->listen();
    }

    /**
     * Place your BotMan logic here.
     */
    public function askName($botman)
    {
        $botman->ask('Hello! What is your Name?', function (Answer $answer) {

            $name = $answer->getText();

            $this->say('Nice to meet you ' . $name);
        });
    }
}
