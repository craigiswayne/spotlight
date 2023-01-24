<?php

namespace App;

use App\UserBase;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Facades\DB;

class Profile extends ProfileBase
{
    public $isUserDefault = false;
    public $userCount = 0;

    protected $visible = ['id', 'name', 'description', 'public', 'writeAccess', 'enabled', 'owner', 'roles'];

    protected $casts = [
        'public' => 'boolean',
        'writeAccess' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function roles()
    {                
        return $this->hasMany(RoleBase::class, 'profileId', 'id');
    }   

    public function owner()
    {                
        return $this->hasOne(UserBase::class, 'id', 'createdByUserId');
    }  
}
