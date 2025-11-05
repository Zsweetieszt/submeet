<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';
    public $timestamps = true;

    protected $fillable = [
        'status',
        'first_paper_sub_id',
        'presenter',
        'nationality_country_id',
        'is_offline',
        'paid_by',
        'event_id'
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'first_paper_sub_id', 'paper_sub_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'paid_by', 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'nationality_country_id', 'country_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class, 'payment_id', 'payment_id');
    }
}
