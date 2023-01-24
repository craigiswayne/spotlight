<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserBase extends Authenticatable
{
    protected $table = 'vw_User';
    public static $snakeAttributes = false;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'overrideProfileId', 'roleId', 'token'
    ];

    protected $visible = [
        'id', 'name'
    ];
}
