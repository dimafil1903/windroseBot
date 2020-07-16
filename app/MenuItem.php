<?php


namespace App;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class MenuItem extends model
{
use Translatable;
    protected $translatable = ['title'];
}
