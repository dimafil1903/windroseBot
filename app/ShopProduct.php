<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;


class ShopProduct extends Model
{
    use Translatable;
    private $translatable=['name','description'];

    public function category()
    {
        return $this->belongsTo(Voyager::modelClass('ShopCategory'));
    }
}
