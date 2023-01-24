<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameStatFeature extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStatFeature';
        
    protected $fillable = [
        'gameId', 'name', 'primaryHitRate', 'secondaryHitRate', 'primaryReturn', 'secondaryReturn', 'position'
    ];

    protected $visible = ['id', 'gameId', 'name', 'primaryHitRate', 'secondaryHitRate', 'primaryReturn', 'secondaryReturn', 'position'];

    const CREATED_AT = 'createdUtcDate';
    const UPDATED_AT = 'modifiedUtcDate';
    
    protected $casts = [
        'id' => 'integer',
        'gameId' => 'integer',
        'position' => 'integer',
        'primaryHitRate' => 'float',
        'secondaryHitRate' => 'float',
        'primaryReturn' => 'float',
        'secondaryReturn' => 'float'
    ];
}