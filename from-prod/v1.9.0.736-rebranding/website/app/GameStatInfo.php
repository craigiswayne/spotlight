<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameStatInfo extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStatInfo';
        
    protected $fillable = [
        'secondaryLabel', 'isSecondaryLabelEnabled'
    ];
    
    protected $visible = ['id',  'secondaryLabel', 'isSecondaryLabelEnabled'];

    const CREATED_AT = 'createdUtcDate';
    const UPDATED_AT = 'modifiedUtcDate';
    
    protected $casts = [
        'id' => 'integer',        
        'isSecondaryLabelEnabled' => 'boolean',        
    ];
}