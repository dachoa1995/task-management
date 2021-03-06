<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function task()
    {
        return $this->hasMany('App\Task');
    }
}
