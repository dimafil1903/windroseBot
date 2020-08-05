<?php
namespace App\Http\Controllers;


use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Payments\PreCheckoutQuery;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;


class TelegramController extends Controller {
    public function set(PhpTelegramBotContract $telegram_bot) {
        return $telegram_bot->setWebhook(env('APP_URL'). '/hooktg');
    }
    public function unset(PhpTelegramBotContract $telegram_bot) {
        return $telegram_bot->deleteWebhook();
    }

    /**
     * Get commands list
     *
     * @param PhpTelegramBotContract $telegram_bot
     * @param \Illuminate\Http\Request $request
     * @return void $commands
     * @throws TelegramException
     */
    public function hook(PhpTelegramBotContract $telegram_bot,\Illuminate\Http\Request $request) {
        Log::info(\GuzzleHttp\json_encode( Request::getInput()));
//       Log::debug(\GuzzleHttp\json_encode($request->json()));
        $telegram_bot->handle();

    }
    public function info(PhpTelegramBotContract $telegram_bot,\Illuminate\Http\Request $request) {

            $telegram_bot->handleGetUpdates();




    }


}
