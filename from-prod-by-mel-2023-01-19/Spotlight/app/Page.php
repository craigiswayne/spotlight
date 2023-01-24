<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\AssetHelper;

class Page extends Model
{
    protected $table = 'vw_Page';

    protected $guarded = [];

    protected $visible = ['id', 'title', 'content', 'category', 'thumbnail', 'assets'];

    public function resources()
    {
        return Resource::where('type', $this->category)->where('belongs_to', $this->id)->orderBy('position')->get();
    }

    public function thumbnailStoragePath($image = null)
    {
        if($image == null) {
            $image = $this->thumbnail;
        }

        return AssetHelper::FromUrl($image);
    }
}
