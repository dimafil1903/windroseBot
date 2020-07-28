<?php
namespace App\Http\Controllers;


use Longman\TelegramBot\Entities\Payments\PreCheckoutQuery;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;


class TelegramController extends Controller {
    public function set(PhpTelegramBotContract $telegram_bot) {
        return $telegram_bot->setWebhook(env('APP_URL'). '/hook');
    }
    public function unset(PhpTelegramBotContract $telegram_bot) {
        return $telegram_bot->deleteWebhook();
    }
    /**
     * Get commands list
     *
     * @return array $commands
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function hook(PhpTelegramBotContract $telegram_bot) {


        $telegram_bot->handle();

    }
    public function info(PhpTelegramBotContract $telegram_bot) {

            $telegram_bot->handleGetUpdates();



    }


}
