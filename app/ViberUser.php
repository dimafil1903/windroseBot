<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ViberUser extends Model
{

    protected $table = "viber_users";
    protected $fillable = ["user_id", "country", "avatar", "name", "language", "api_version", "lang"];

}
