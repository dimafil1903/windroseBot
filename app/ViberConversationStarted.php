<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ViberConversationStarted extends Model
{

    protected $table = "viber_conversation_started";
    protected $fillable = ["user_id",
        "timestamp",
        "message_token",
        "chat_hostname",
        "type",
        "subscribed"];
}
