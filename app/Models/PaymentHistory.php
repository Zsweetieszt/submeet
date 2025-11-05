<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = 'payment_history';

    protected $primaryKey = 'payment_history_id';

    protected $fillable = [
        'payment_id',
        'brivano',
        'expired_date',
        'receipt',
        'desc',
        'upload_receipt_at',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }
}
