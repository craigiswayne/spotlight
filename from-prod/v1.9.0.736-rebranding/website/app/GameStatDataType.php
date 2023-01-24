<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameStatDataType extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStatDataType';
        
    protected $fillable = [
        'name'
    ];
    
    protected $visible = ['id',  'name'];
        
    protected $casts = [
        'id' => 'integer'      
    ];   
}