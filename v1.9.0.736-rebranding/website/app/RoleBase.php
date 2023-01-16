<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleBase extends Authenticatable
{
    protected $table = 'vw_Role';    
    public static $snakeAttributes = false;
    
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'typeId', 'profileId', 'system', 'deleted'
    ];

    protected $visible = [
        'id', 'name', 'description', 'type', 'profile', 'system'
    ];  
    
    protected $casts = [
        'system' => 'boolean',
        'deleted' => 'boolean',
        'typeId' => 'integer'
    ];

    public function type()
    {
        return $this->hasOne(RoleType::class, 'id', 'typeId');
    }

    public function profile()
    {
        return $this->hasOne(ProfileBase::class, 'id', 'profileId');
    }
}
