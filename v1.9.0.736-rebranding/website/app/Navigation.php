<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Navigation extends Model
{
    protected $table = 'vw_Navigation';

    const CREATED_AT = 'createdUtcDate';
    const UPDATED_AT = 'modifiedUtcDate';

    protected $guarded = [];

    protected $visible = ['id', 'name', 'url', 'thumbnail', 'hostingTypeId', 'position'];

    static function query() {

        $model = new Navigation();

        if(is_export())
        {
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Navigation_Get
                                        @ProfileId = ?
                                        ,@HostingTypeId = ?",
                            array(export_profile_id(), 3)));   
        }
        else
        {            
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Navigation_Get
                                         @LoggedInUserId = ?",
                            array(auth()->user()->id, null)));   
        }

       
        return $data;                    
    }

}
