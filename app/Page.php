<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class Page extends Model
{
    use Resizable;

    protected $table = 'pages';

    public $fillable = [
        'id',
        'title',
        'slug',
        'text',
        'sort',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

}
