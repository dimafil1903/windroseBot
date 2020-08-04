<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class LastMessage extends Model
{

    protected $table = "last_message";
    protected $fillable = ["message_id", "user_id", "text", "checked"];

}
