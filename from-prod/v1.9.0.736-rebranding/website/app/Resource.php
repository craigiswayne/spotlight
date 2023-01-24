<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    protected $table = 'vw_Resource';

    protected $guarded = [];

    protected $appends = ['slug', 'hyperlink'];

    protected $visible = ['id', 'title', 'asset_path', 'slug', 'href', 'width', 'height', 'size'];

    public static function types()
    {
        $types = [
            'test-resource',
            'navigation-cards',
            'showreel',                    
            'third-party-providers',            
            'game-symbols',
            'game-portraits',
            'game-maths',
            'game-trailers',
            'play-it-forward',
            'regulated-market-logos',
        ];

        foreach (GameBase::availableFeatures() as $feature) {
            array_push($types, $feature.'-features');
        }
        return $types;
    }

    public function getSlugAttribute()
    {
        return Str::slug($this->title);
    }

    public function getHyperlinkAttribute()
    {
        if (strpos($this->title, 'http') !== false || in_array($this->title, ['#'])) {
            return $this->title;
        }

        return '/'.Str::slug($this->title);
    }
}
