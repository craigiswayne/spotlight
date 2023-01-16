<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileSetting extends Model
{
    protected $table = 'vw_ProfileSetting';

    protected $guarded = [];

    protected $visible = ['id', 'name', 'value', 'valueTypeId'];

    protected $casts = [        
        'id' => 'integer',
        'valueTypeId' => 'integer'
    ];

    static function query() {

        $model = new ProfileSetting();

        if(is_export())
        {
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Setting_Get
                                         @ProfileId = ?
                                        ,@HostingTypeId = ?",
                            array(export_profile_id(), 3)));   
        }
        else
        {            
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Setting_Get
                                         @LoggedInUserId = ?",
                            array(auth()->user()->id, null)));   
        }

       
        return $data;     
    }

    static function settings() {
        return ProfileSetting::query()->mapWithKeys(function ($item, $index)
        {     
           $value = null;
            switch($item->valueTypeId)
            {
                case 1: 
                    $value = ($item->value == "1");
                break;
                case 2:
                    $value =  intval($item->value);        
                default:
                $value  = $item->value; 
            }


            return [$item->name => $value];
            
        });
    }

}
