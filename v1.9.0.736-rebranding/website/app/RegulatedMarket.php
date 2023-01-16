<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegulatedMarket extends Model
{
    protected $table = 'vw_RegulatedMarket';

    protected $guarded = [];
    
    protected $appends = ['logos'];

    public function logos()
    {
        return Resource::where('type', 'regulated-market-logos')->where('belongs_to', $this->id)->orderBy('position')->get();
    }

    public function getLogosAttribute()
    {
        return $this->logos();
    }

    public function next()
    {   
        $next = RegulatedMarket::where('id', '>', $this->id)->first();

        if($next){
            return '<a href="/admin/markets/'.$next->id.'">'.$next->country_name.'<i class="material-icons ml-2">keyboard_arrow_right</i></a>';
        }
        else return null;
        
    }

    public function prev()
    {
        $prev = RegulatedMarket::where('id', '<', $this->id)->first();

        if($prev){
            return '<a href="/admin/markets/'.$prev->id.'"><i class="material-icons mr-2">keyboard_arrow_left</i>'.$prev->country_name.'</a>';
        }
        else return null;
    }
}
