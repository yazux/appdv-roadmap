<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class Project extends Model
{
    use Resizable;

    protected $table = 'projects';

    public $fillable = [
        'id',
        'name',
        'description',
        'percent',
        'photo',
        'photos',
        'videos',
        'start_date',
        'end_date',
        'status',
        'docs',
        'vendor_id',
        'price',
        'created_at',
        'updated_at'
    ];

    public $statuses = [
        'planned' => 'Проектировка/В плане',
        'work' => 'В работе',
        'done' => 'Закончен'
    ];

    public $timestamps = true;

    public function getStatusNameAttribute()
    {
        return $this->statuses[$this->status];
    }

    
    /**
     * Связь с заказчиком
     * @return mixed
     */
    public function vendor()
    {
        return $this->belongsTo('App\Vendor', 'vendor_id');
    }
}
