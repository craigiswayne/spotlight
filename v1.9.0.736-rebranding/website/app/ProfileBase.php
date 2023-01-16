<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Facades\DB;

class ProfileBase extends Model
{
    public $isUserDefault = false;
    public $userCount = 0;

    protected $table = 'vw_Profile';
    protected $keyType = 'string';

    protected $guarded = [];

    protected $visible = [
        'id', 'name', 'description'
    ];
}
