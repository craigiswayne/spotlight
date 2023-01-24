<?php

namespace App\Helpers;
use App\User;

class SecureHelper
{
    public static function hasNormalAccess(...$actions)
    {
        return SecureHelper::hasAccess('Normal', ...$actions);
    }

    public static function hasAdminAccess(...$actions)
    {
        return SecureHelper::hasAccess('Admin', ...$actions);
    }

    private static function hasAccess($type, ...$actions)
    {
        $access = false;        
        foreach($actions as $container) {
            $action = $container;
            if(is_array($action) && count($action) > 0) {
                $action = $action[0];
            }

            $parts = explode('|', $action);
            if(count($parts) != 2) {
                throw `Invalid permission guard of '{$action}'`;
            }
            
            if(!isset(auth()->user()->permissions[$type][$parts[0]])) {
                continue;
            }

            $securables = auth()->user()->permissions[$type][$parts[0]]->toArray();
     
            if(!in_array($parts[1], $securables)) {
                continue;
            } 
            
            $access = true;
        }

        return $access;
    }
}