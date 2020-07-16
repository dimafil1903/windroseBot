<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;
use App\MenuItem;

class TelegramSetting extends Model
{

    use Translatable;
    protected $translatable = ['start_message','message_on_success_lang_change','prefix_exit','postfix_exit'];


}
