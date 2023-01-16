<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorageAsset extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_StorageAsset';
        
    protected $fillable = [
        'url'
    ];

    protected $visible = ['url'];
}