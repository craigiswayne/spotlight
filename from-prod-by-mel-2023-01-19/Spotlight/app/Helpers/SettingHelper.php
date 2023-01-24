<?php

namespace App\Helpers;

class SettingHelper
{
    public static function Boolean($name)
    {
        $value = SettingHelper::GetValue($name);
        if($value == null) {
            return false;
        }

        return boolval($value);
    }

    private static function GetValue($name)
    {
        if(!$name) {
            return null;
        }

        $settings = \App\ProfileSetting::query();
        if($settings == null) {
            return null;
        }

        return $settings->where('name', '=', $name)->first()->value;
    }
}