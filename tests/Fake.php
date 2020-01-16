<?php

namespace Pebble\Translatable\Tests;

use Illuminate\Database\Eloquent\Model;
use Pebble\Translatable\Traits\Translatable;

class Fake extends Model
{
    use Translatable;

    protected $fillable = ['greeting'];
    protected $guarded = ['id'];
    public static $translatable = ['greeting'];
}
