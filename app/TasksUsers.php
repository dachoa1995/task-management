<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TasksUsers extends Model
{
    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
