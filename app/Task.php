<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function tasksUsers()
    {
        return $this->hasMany('App\TasksUsers');
    }

    public function users() {
        return $this->belongsToMany('App\User');
    }
}
