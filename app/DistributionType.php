<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;


class DistributionType extends Model
{
    use Translatable;
    private $translatable=['name'];
}
