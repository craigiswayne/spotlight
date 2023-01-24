<?php

namespace App;
use App\Helpers\AssetHelper;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'vw_Product';

    protected $guarded = [];
    
    protected $visible = ['id', 'name', 'thumbnail', 'features'];
    
    public function features()
    {
        return $this->hasMany(ProductFeature::class);
    }

    public function thumbnailStoragePath($image = null)
    {
        if($image == null) {
            $image = $this->thumbnail;
        }

        return AssetHelper::FromUrl($image);
    }
}
