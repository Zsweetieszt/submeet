<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{

    protected $table = 'user_subscriptions';
  
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'plan_code',   
        'plan_name',
        'price',
        'max_events',  
        'desc',        
        'status',
        'payment_proof',
        'starts_at',
        'ends_at',
    ];

  
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price' => 'decimal:2',
        'max_events' => 'integer',
    ];

    public function user()
    {

        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}