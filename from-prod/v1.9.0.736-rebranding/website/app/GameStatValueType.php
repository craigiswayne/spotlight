<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameStatValueType extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStatValueType';
        
    protected $fillable = [
        'name', 'dataTypeId'
    ];
    
    protected $visible = ['id',  'name', 'dataType'];
        
    protected $casts = [
        'id' => 'integer'      
    ];   

    public function dataType()
    {                
        return $this->hasOne(GameStatDataType::class, 'id', 'dataTypeId');
    }  
}