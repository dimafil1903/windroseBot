<?php


namespace App\Messenger;


use App\MessengerUser;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Interfaces\UserInterface;
use BotMan\BotMan\Users\User;

class DB
{
    /**
     * @var BotManFactory
     */
    private $bot;

    /**
     * @var MessengerUser
     */
    /**
     * @param $user UserInterface
     * @return MessengerUser
     */
    public function insertUser($user)
    {

      return  MessengerUser::updateOrCreate([
            'user_id' => $user->getId()
        ], [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ]);

    }

    public function insertData()
    {

    }
}
