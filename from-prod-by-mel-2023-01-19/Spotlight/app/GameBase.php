<?php

namespace App;

use App\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GameBase extends Model
{
    protected $table = 'vw_Game';

    protected $guarded = [];

    protected $appends = ['thumbnail'];

    protected $visible = ['id', 'name', 'featured', 'new', 'mirrorCharacter', 'thumbnail', 'thumbnails', 'background', 'logo', 'character', 'studioId', 'studio', 'rowIndex', 'columnIndex', 'width', 'height', 'newMaths', 'goLiveMonth', 'symbols', 'portraits', 'maths', 'trailers'];

    protected $casts = [
        'new' => 'boolean',
        'featured' => 'boolean',
        'mirrorCharacter' => 'boolean',
        'id' => 'integer',
        'studioId' => 'integer',
        'rowIndex' => 'integer',
        'columnIndex' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'newMaths' => 'boolean',
        'goLiveMonth' => 'datetime',
		'deleted' => 'boolean'
    ];

    public function getThumbnailAttribute()
    {
        $default = GameAsset::where([['gameId', $this->id],['assetTypeId', 1]])->orderby('position')->first();

        if($default == null) {
            return null;
        }

        return $default->url;
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class, 'studioId', 'id');
    }

    public function thumbnails()
    {
        return $this->hasMany(GameAsset::class, 'gameId', 'id')->where('assetTypeId', 1)->orderby('position');
    }

    public function symbols()
    {
        return $this->hasMany(Resource::class, 'belongs_to', 'id')->where('type', 'game-symbols')->orderBy('position');
    }

    static function availableFeatures()
    {
        $features = [];

        foreach (Resource::where('type','like', '%-features')->get()->groupBy('type') as $feature => $val) {
            $features[] = str_replace('-features', '', $feature);
        }

        if(cache('game-features')){
            foreach (json_decode(cache('game-features')) as $feature) {
                $features[] = $feature;
            }
        }

        return array_unique($features);
    }

    public static function newGameFeatures() {
        $features = [];

        foreach(GameBase::availableFeatures() as $feature){
            array_push($features, [
                'name' => ucwords( str_replace('-', ' ', $feature) ),
                'slug' => $feature,
                'items' => []
            ]);
        }
        return $features;
    }

    public function features($type = null)
    {
        if($type == null){
            $features = [];

            foreach($this->availableFeatures() as $feature){
                $items = Resource::where('type', $feature.'-features')->where('belongs_to', $this->id)->orderBy('position')->get()->toArray();

                $newFeature = [
                    'name' => ucwords( str_replace('-', ' ', $feature) ),
                    'slug' => $feature,
                    'items' => []
                ];

                if(sizeof($items) > 0){
                    $newFeature['items'] = $items;
                }

                array_push($features, $newFeature);
            }
            return $features;
        }
        else return Resource::where('type', $type.'-features')->where('belongs_to', $this->id)->orderBy('position')->get();
    }

    public function portraits()
    {
        return $this->hasMany(Resource::class, 'belongs_to', 'id')->where('type', 'game-portraits')->orderBy('position');
    }

    public function maths()
    {
        return $this->hasMany(Resource::class, 'belongs_to', 'id')->where('type', 'game-maths')->orderBy('position');
    }

    public function stats()
    {
        return $this->hasMany(GameStat::class, 'gameId', 'id')->orderBy('position');
    }

    public function trailers()
    {
        return $this->hasMany(Resource::class, 'belongs_to', 'id')->where('type', 'game-trailers')->orderBy('position');
    }

    static function profile() {

        $model = new GameBase();

        if(is_export())
        {
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Game_Get
                                         @ProfileId = ?",
                            array(export_profile_id())));
        }
        else
        {
            $data = $model->hydrate(
                DB::select("EXEC slt.pr_Profile_Game_Get
                                         @LoggedInUserId = ?",
                            array(auth()->user()->id)));
        }


        return $data;
    }

    static function convertBoolToBit($val) {
        if(is_null($val)) {
            return null;
        }

        return $val ? 1 : 0;
    }

    static function gamesJson($isNew, $isFeatured) {

        if(is_export())
        {
            return DB::selectOne("EXEC slt.pr_Profile_Game_GetJson
                                    @ProfileId = ?
                                   ,@IsNew = ?
                                   ,@IsFeatured = ?
                                   ,@IsExport = 1",
                        array(export_profile_id(), GameBase::convertBoolToBit($isNew), GameBase::convertBoolToBit($isFeatured)))->json;
        }
        else
        {
            return DB::selectOne("EXEC slt.pr_Profile_Game_GetJson
                                         @LoggedInUserId = ?
                                        ,@IsNew = ?
                                        ,@IsFeatured = ?
                                        ,@IsExport = 0",
                            array(auth()->user()->id, GameBase::convertBoolToBit($isNew), GameBase::convertBoolToBit($isFeatured)))->json;
        }
    }

    static function getOffetGame($id, $offset) {
        $offsetId = DB::select("SELECT slt.fn_Game_Position_Offset_Get(?, ?) [id]", array($id, $offset))[0]->id;

        if($offsetId == null) {
            return null;
        }

        return GameBase::where('id', $offsetId)->first();
    }
}