<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\AssetHelper;
use Illuminate\Support\Facades\DB;

class Studio extends Model
{
    protected $table = 'vw_Studio';

    protected $guarded = [];

    protected $visible = ['id', 'image', 'name', 'video'];

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function imageStoragePath()
    {
        return AssetHelper::FromUrl($this->image);
    }

    public function videoStoragePath()
    {
        return AssetHelper::FromUrl($this->video);
    }

    static function query() {

        $model = new Studio();

        if(is_export())
        {
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Studio_Get
                                         @ProfileId = ?",
                            array(export_profile_id())));   
        }
        else
        {            
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Studio_Get
                                         @LoggedInUserId = ?",
                            array(auth()->user()->id)));   
        }

       
        return $data;     
    }
}
