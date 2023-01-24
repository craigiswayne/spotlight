<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameStatType extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStatType';
        
    protected $fillable = [
        'name', 'iconId', 'valueTypeId', 'vertical', 'big', 'position', 'custom', 'deleted'
    ];
    
    protected $visible = ['id',  'name', 'icon', 'valueType', 'vertical', 'big', 'position', 'custom', 'deleted'];

    const CREATED_AT = 'createdUtcDate';
    const UPDATED_AT = 'modifiedUtcDate';
    
    protected $casts = [
        'id' => 'integer',
        'iconId' => 'integer',
        'valueTypeId' => 'integer',
        'vertical' => 'boolean',
        'big' => 'boolean',
        'position' => 'integer',
        'custom' => 'boolean',
        'deleted' => 'boolean',
    ];

    public function icon()
    {                
        return $this->hasOne(GameStatIcon::class, 'id', 'iconId');
    }  

    public function valueType()
    {                
        return $this->hasOne(GameStatValueType::class, 'id', 'valueTypeId');
    }  
}