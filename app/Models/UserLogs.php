<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogs extends Model
{
    
protected $table = 'users_logs';

protected $primaryKey = 'user_log_id';

protected $fillable = [
    'user_id',
    'ip_address',	
    'user_log_type',
    'user_agent',
    'created_at',
];

public function user()
{
    return $this->belongsTo(User::class, 'user_id', 'user_id');
}

public $timestamps = false;
}
