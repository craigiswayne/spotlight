<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GameStat extends Model
{
    public static $snakeAttributes = false;
    protected $table = 'vw_GameStat';
        
    protected $fillable = [
        'gameId', 'gameStatTypeId', 'name', 'vertical', 'primaryNumberValue', 'primaryTextValue', 'secondaryNumberValue', 'secondaryTextValue', 'enabled', 'position'
    ];

    protected $appends = ['type', 'primaryValue', 'secondaryValue'];

    protected $visible = ['id',  'gameId', 'type', 'name', 'vertical', 'primaryValue', 'secondaryValue', 'enabled', 'position'];

    const CREATED_AT = 'createdUtcDate';
    const UPDATED_AT = 'modifiedUtcDate';
    
    protected $casts = [
        'id' => 'integer',
        'gameId' => 'integer',    
        'position' => 'integer',
        'vertical' => 'boolean',
        'enabled' => 'boolean',       
        'primaryNumberValue' => 'float',
        'secondaryNumberValue' => 'float',
    ];

    public function getTypeAttribute()
    {           
        return GameStatType::with('icon', 'valueType', 'valueType.dataType')->where('id', $this->gameStatTypeId)->first();
    }

    public function getPrimaryValueAttribute()
    {           
        if($this->type->valueType->dataType->id == 1) {
            return $this->primaryNumberValue;
        } else {
            return $this->primaryTextValue;
        }        
    }

    public function getSecondaryValueAttribute()
    {           
        if($this->type->valueType->dataType->id == 1) {
            return $this->secondaryNumberValue;
        } else {
            return $this->secondaryTextValue;
        }        
    }

    public static function getStats($gameId) {

        $model = new GameStat();        
        $data = $model->hydrate(
            DB::select("EXEC slt.pr_GameStat_Get
                                    @GameId = ?",
                        array($gameId)));   
       
        return $data;     
    }
}