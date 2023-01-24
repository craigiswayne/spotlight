<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\AssetHelper;

class ProductFeature extends Model
{
    protected $table = 'vw_ProductFeature';

    protected $visible = ['id', 'name', 'content', 'icon', 'position'];

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function iconStoragePath($image = null)
    {
        if($image == null) {
            $image = $this->icon;
        }

        return AssetHelper::FromUrl($image);
    }
}
