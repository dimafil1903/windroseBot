<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ViberConversation extends Model
{

    protected $table="viber_conversation";
    protected $fillable=["user_id","chat_id","status","command","notes"];
}
