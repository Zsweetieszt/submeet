<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    protected $table = 'users_events';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'user_id',
        'event_id',
        'role_id',
        'is_offline'
    ];

    public $timestamps = true;

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function event(){
        return $this->hasOne(Event::class, 'event_id', 'event_id');
    }

    public function role(){
        return $this->hasOne(Role::class, 'role_id', 'role_id');
    }
}
