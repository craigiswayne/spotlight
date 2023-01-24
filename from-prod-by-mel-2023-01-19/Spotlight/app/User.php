<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends UserBase
{
    use Notifiable;

    protected $visible = [
        'id', 'name', 'email', 'role', 'profile',  'permissions'
    ];

    protected $casts = [
        'roleId' => 'integer',
    ];

    protected $appends = [
        'profile', 'permissions'
    ];

    public function role()
    {                
        return $this->hasOne(RoleBase::class, 'id', 'roleId');
    }   

    function getPermissionsAttribute() {

        return RoleSecurableAction::where('roleId', $this->roleId)->orderBy("name")->get()->groupBy('roleType')->map(function ($roleTypes)
        { 
            return $roleTypes->groupBy('securable')->map(function ($securables)
            { 
                return $securables->pluck('name');         
            });
        });
    }

    public function getProfileAttribute()
    {                
        if($this->overrideProfileId != null) {
            return ProfileBase::where('id', $this->overrideProfileId)->first();
        } else {
            return ProfileBase::where('id', $this->role->profileId)->first();

        }
    } 

    public function getIsAdminAttribute()
    {
        return $this->role->typeId >= 2;
    }
}
