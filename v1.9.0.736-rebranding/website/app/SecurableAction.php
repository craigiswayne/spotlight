<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SecurableAction extends Authenticatable
{
    protected $table = 'vw_SecurableAction';
   
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'securableId', 'name', 'description', 'createdUtcDate', 'modifiedUtcDate', 'position'
    ];

    protected $visible = [
        'id', 'securableId', 'name', 'description', 'createdUtcDate', 'modifiedUtcDate'
    ];   

    public function securable() {
        return $this->belongsTo(Securable::class, 'securableId');
    }
    
}
