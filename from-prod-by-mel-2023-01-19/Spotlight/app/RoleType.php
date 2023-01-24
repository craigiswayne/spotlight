<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleType extends Authenticatable
{
    protected $table = 'vw_RoleType';
   
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    protected $visible = [
        'id', 'name'
    ];   
    
}
