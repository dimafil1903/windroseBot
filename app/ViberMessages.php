<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ViberMessages extends Model
{

    protected $table = "viber_messages";
    protected $fillable = [
        "user_id",
        "timestamp",
        "message_token",
        "chat_hostname",
        "type",
        "text",
        "media",
        "sticker_id",
        "contact",
        "file_name",
        "thumbnail",
        "location",
    ];
}
