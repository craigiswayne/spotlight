<?php

namespace App;

class GameNew extends GameBase
{
    protected $appends = ['thumbnail'];
    
    public function getThumbnailAttribute()
    {
        $active = GameAsset::where([['gameId', $this->id],['assetTypeId', 1],['active', 1]])->first();
        if($active == null) {
            return parent::getThumbnailAttribute();
        }
               
        return $active->url; 
    }
}