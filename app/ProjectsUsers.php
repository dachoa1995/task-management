<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsUsers extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User', 'foreign_key', 'user_id');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
