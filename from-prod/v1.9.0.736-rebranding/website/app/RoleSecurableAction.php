<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleSecurableAction extends Authenticatable
{
    protected $table = 'vw_RoleSecurableAction';    
    public static $snakeAttributes = false;
    
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [      
    ];

    protected $visible = [
        'id', 'name', 'roleType'
    ];  

    protected $casts = [
        'roleId' => 'integer',        
        'id' => 'integer'
    ];
    
}
