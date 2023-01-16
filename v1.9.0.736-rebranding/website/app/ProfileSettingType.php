<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProfileSettingType extends Model
{
    protected $table = 'vw_ProfileSettingType';

    protected $guarded = [];

    protected $visible = ['id', 'name', 'description', 'valueTypeId', 'hostingTypeId', 'defaultBitValue', 'defaultIntValue', 'defaultTextValue'];
}
