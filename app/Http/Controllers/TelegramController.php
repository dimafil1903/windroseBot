<?php
namespace App\Http\Controllers;


use Longman\TelegramBot\Entities\Payments\PreCheckoutQuery;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;


class TelegramController extends Controller {
    public function set(PhpTelegramBotContract $telegram_bot) {
        try {
            return $telegram_bot->setWebhook(env('APP_URL') . '/hook');
        } catch (TelegramException $e) {
        }
    }
    public function unset(PhpTelegramBotContract $telegram_bot) {
        try {
            return $telegram_bot->deleteWebhook();
        } catch (TelegramException $e) {
        }
    }

    /**
     * Get commands list
     *
     * @param PhpTelegramBotContract $telegram_bot
     * @return void $commands
     * @throws TelegramException
     */
    public function hook(PhpTelegramBotContract $telegram_bot) {


        $telegram_bot->handle();

    }
    public function info(PhpTelegramBotContract $telegram_bot) {

        try {
            $telegram_bot->handleGetUpdates();
        } catch (TelegramException $e) {
        }


    }


}
