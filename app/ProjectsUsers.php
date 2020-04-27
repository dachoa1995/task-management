<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectsUsers extends Model
{
    protected $table = 'project_user';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
