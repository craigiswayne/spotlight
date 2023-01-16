<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameStatIcon extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStatIcon';
        
    protected $fillable = [
        'name'
    ];
    
    protected $visible = ['id',  'name'];
        
    protected $casts = [
        'id' => 'integer'      
    ];   
}