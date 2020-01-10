<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TasksUsers extends Model
{
    protected $table = 'task_user';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
