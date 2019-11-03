<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class ImportTask extends Model
{
    use Resizable;

    protected $table = 'import_tasks';

    public $fillable = [
        'id',
        'task',
        'status',
        'date',
        'time'
    ];
    public $timestamps = false;

    public function setStatus($status)
    {
        if (!$status || !is_numeric($status) || $status > 3) return;
        $this->update(['status' => $status]);
        return $this;
    }

    public function setTime($time)
    {
        if (!$time) return;
        $this->update(['time' => $time]);
        return $this;
    }

    public function setCompleteDate()
    {
        $this->update(['date' => date('Y-m-d H:i:s')]);
        return $this;
    }
}
