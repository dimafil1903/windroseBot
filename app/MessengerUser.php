<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessengerUser extends Model
{
    protected $table="messenger_users";
    protected $fillable=['user_id','first_name','last_name','username','lang','phone'];
    /**
     * @var mixed|string
     */


}
