<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Securable extends Authenticatable
{
    protected $table = 'vw_Securable';
    protected $with = ['actions'];
    public static $snakeAttributes = false;
    
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'roleTypeId', 'deleted', 'createdUtcDate', 'modifiedUtcDate', 'position'
    ];

    protected $visible = [
        'id', 'name', 'roleType', 'deleted', 'createdUtcDate', 'modifiedUtcDate', 'actions'
    ];

    public function actions()
    {
        return $this->hasMany(SecurableAction::class, 'securableId', 'id');
    }

    public function roleType() {
        return $this->belongsTo(RoleType::class, 'roleTypeId', 'id');
    }
    
}
