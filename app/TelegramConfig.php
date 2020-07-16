<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Events\SettingUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;
use App\MenuItem;
class TelegramConfig extends Model
{
use Translatable;
    protected $translatable = ['value'];

    protected $table = 'telegram_configs';


    protected $guarded = [];

    public $timestamps = false;





}
