<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Chat;
use App\Telegram\Helpers\GetApi;
use App\Telegram\Helpers\GetMessageFromData;
use Illuminate\Support\Facades\Lang;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

/**
 * Inline query command
 *
 * Command that handles inline queries.
 */
class InlinequeryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'inlinequery';

    /**
     * @var string
     */
    protected $description = 'Reply to inline query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Command execute method
     *
     * @return void
     * @throws TelegramException
     */
    public function execute()
    {
        $inline_query = $this->getInlineQuery();
        $query = $inline_query->getQuery();
//        dd($inline_query->getOffset());
        $data = ['inline_query_id' => $inline_query->getId()];
        $results = [];
        $queryPiece = explode(" ", $query);
        $date = "";
        $flightNumber = "";
        if (isset($queryPiece)) {
            $date = $queryPiece[1];
        }
        if (isset($queryPiece)) {
            $flightNumber = $queryPiece[0];
        }
        $flightNumber = explode("-", $flightNumber);
        $flightNumberWithoutCarrier = $flightNumber[1];
//        dd($date,$flightNumberWithoutCarrier);
        $flight = GetApi::getOneFlight($date, $flightNumberWithoutCarrier);
//        dd($flight);
//        dd($flight);
        if (isset($flight->code))
            if ($flight->code == 404) {
                dd($flight);
            }
        $array = [];
        $miniArray = [
            ['text' => "Отслеживать вместе со мной", 'url' => "t.me/windroseHelpBot?start="],
        ];
        $chat = Chat::find($inline_query->getFrom()->getId());
        $langAPI = $lang = $chat->lang;
        array_push($array, $miniArray);
        $from = (array)$flight->from;
        $to = (array)$flight->to;

        //$langAPI = $lang;
        if ($lang == "uk") $langAPI = "ua";


        $inline_keyboard = new InlineKeyboard($array);
        if ($query !== '') {
            $articles = [
                [
                    'type' => 'article',
                    'id' => '001',
                    'title' => $flight->carrier . "-" .
                        $flight->flight_number . " " .
                        $from[$langAPI] . "-" .
                        $to[$langAPI],
                    "thumb_url" => asset("storage/Img/logo.jpg"),
                    'description' => Lang::get("messages.clickToSend", [], "$lang"),
                    'reply_markup' => $inline_keyboard,
                    'input_message_content' =>
                        new InputTextMessageContent(
                            [
                                'message_text' => GetMessageFromData::generateCard($flight, $lang),
                            ]
                        ),

                ],
            ];

            foreach ($articles as $article) {
                $results[] = new InlineQueryResultArticle($article);
            }
        }

        $data['results'] = '[' . implode(',', $results) . ']';

        Request::answerInlineQuery($data);
    }
}
