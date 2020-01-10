<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description'
    ];

    public function projectsUsers()
    {
        return $this->hasMany('App\ProjectsUsers');
    }

    public function status()
    {
        return $this->hasMany('App\Status');
    }

    public function users() {
        return $this->belongsToMany('App\User');
    }
}
