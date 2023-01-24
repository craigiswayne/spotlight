<?php

namespace App\Helpers;

class AssetHelper
{
    
    static $storage_prefix = '/assets/storage/';

    public static function FromUrl($url)
    {
        return str_replace(AssetHelper::$storage_prefix, '',  str_replace("\\", "/",  $url));        
    }

    public static function ToUrl($path)
    {
        return AssetHelper::$storage_prefix.str_replace("\\", "/",  $path);        
    }
}