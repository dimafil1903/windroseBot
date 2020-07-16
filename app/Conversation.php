<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table="conversation";
    protected $fillable=['user_id','chat_id','status','command','notes'];
}
