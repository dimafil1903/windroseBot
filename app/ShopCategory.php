<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;
class ShopCategory extends Model
{

    use Translatable;
    private $translatable=['name'];
    public function shopProducts()
    {
        return $this->hasMany(Voyager::modelClass('ShopProduct'))
            ->orderBy('created_at', 'DESC');
    }
    public function parentId()
    {
        return $this->belongsTo(self::class);
    }
}
