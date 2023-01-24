<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameAsset extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameAsset';
        
    protected $fillable = [
        'gameId', 'assetTypeId', 'url', 'active', 'position'
    ];

    protected $visible = ['id', 'url', 'active', 'position'];

    const CREATED_AT = 'createdUtcDate';
    const UPDATED_AT = 'modifiedUtcDate';
    
    protected $casts = [
        'id' => 'integer',
        'gameId' => 'integer',
        'assetTypeId' => 'integer',        
        'active' => 'boolean',
        'position' => 'integer',
    ];
}