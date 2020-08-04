<?php


namespace App\Viber;


use App\LastMessage;
use App\ViberConversationStarted;
use App\ViberMessages;
use App\ViberUser;
use Illuminate\Support\Facades\Log;
use Paragraf\ViberBot\Event\ConversationStartedEvent;

class DB extends ViberBot
{


    public function startedConversation()
    {
        $user = new \Paragraf\ViberBot\Model\ViberUser($this->getRequest()->user['id'], $this->getRequest()->user['name']);
        Log::alert(\GuzzleHttp\json_encode($this->getRequest()->user));
        $userObj = (object)$this->getRequest()->user;
        if (isset($this->getRequest()->user['avatar'])) {
            $user->setAvatar($this->getRequest()->user['avatar']);
        } else {
            $user->setAvatar("");
        }
        ViberUser::updateOrCreate(
            ["user_id" => $user->getId()],
            [
                "name" => $user->getName(),
                "avatar" => $user->getAvatar(),
                "country" => $userObj->country,
                "language" => $userObj->language,
                "api_version" => $userObj->api_version,
            ]

        );

        ViberConversationStarted::create([
            "user_id" => $user->getId(),
            "timestamp" => $this->getRequest()->timestamp,
            "message_token" => $this->getRequest()->message_token,
            "chat_hostname" => $this->getRequest()->chat_hostname,
            "type" => $this->getRequest()->type,
            "subscribed" => $this->getRequest()->subscribed,
        ]);
    }

    public function Message()
    {
        $userObj = (object)$this->getRequest()->sender;

        $data1 = [
            "user_id" => $userObj->id,
            "message_token" => $this->getRequest()->message_token,
        ];
        $data2 = [
            "user_id" => $userObj->id,

        ];
        $data = [

            "timestamp" => $this->getRequest()->timestamp,

            "chat_hostname" => $this->getRequest()->chat_hostname,
            "type" => $this->getRequest()->message["type"],
        ];
        if (isset($this->getRequest()->message["text"])) {
            $data["text"] = $this->getRequest()->message["text"];
        }
        if (isset($this->getRequest()->message["media"])) {
            $data["media"] = $this->getRequest()->message["media"];
        }
        if (isset($this->getRequest()->message["sticker_id"])) {
            $data["sticker_id"] = $this->getRequest()->message["sticker_id"];
        }
        if (isset($this->getRequest()->message["contact"])) {
            $data["contact"] = $this->getRequest()->message["contact"];
        }
        if (isset($this->getRequest()->message["file_name"])) {
            $data["file_name"] = $this->getRequest()->message["file_name"];
        }
        if (isset($this->getRequest()->message["thumbnail"])) {
            $data["thumbnail"] = $this->getRequest()->message["thumbnail"];
        }
        if (isset($this->getRequest()->message["location"])) {
            $data["location"] = $this->getRequest()->message["location"];
        }
        $m = ViberMessages::updateOrCreate($data1, $data);
        $data["message_id"] = $m->id;
        LastMessage::updateOrCreate($data2, $data);
    }

    public function insertData()
    {

        Log::debug($this->getEvent());
        if ($this->getEvent() == 'conversation_started') {
            $this->startedConversation();
        } elseif ($this->getEvent() == 'message') {
            $this->Message();
        }

    }
}
