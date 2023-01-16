<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Role extends RoleBase
{    
    use Notifiable;

    protected $visible = [
        'id', 'name', 'description', 'type', 'profile', 'actions', 'users', 'system'
    ];  
    
    protected $appends = [
        'actions', 'users'
    ];
    
    function getUsersAttribute() {
        return array_map(function($item) {
            return $item['id'];
        },  User::where('roleId', $this->id)->orderBy("id")->get()->toArray());
    }

    function getActionsAttribute() {
        return array_map(function($item) {
            return $item['id'];
        }, RoleSecurableAction::where('roleId', $this->id)->orderBy("id")->get()->toArray());
    }
}
