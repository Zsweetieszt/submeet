<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettings extends Model
{
    protected $primaryKey = 'pay_set_id';
        
        protected $fillable = [
            'event_id',
            'pay_as_pstr_on_ntl',
            'pay_as_pstr_on_ntl_curr',
            'pay_as_pstr_on_ntl_amount',
            'pay_as_pstr_off_ntl',
            'pay_as_pstr_off_ntl_curr',
            'pay_as_pstr_off_ntl_amount',
            'pay_as_npstr_off_ntl',
            'pay_as_npstr_off_ntl_curr',
            'pay_as_npstr_off_ntl_amount',
            'pay_as_pstr_on_intl',
            'pay_as_pstr_on_intl_curr',
            'pay_as_pstr_on_intl_amount',
            'pay_as_pstr_off_intl',
            'pay_as_pstr_off_intl_curr',
            'pay_as_pstr_off_intl_amount',
            'acc_beneficiary_name',
            'acc_bank_name',
            'acc_bank_acc',
            'acc_swift_code',
            'created_by',
            'updated_by'
        ];

        public function event()
        {
            return $this->belongsTo(Event::class, 'event_id', 'event_id');
        }

        public function creator()
        {
            return $this->belongsTo(User::class, 'created_by', 'user_id');
        }

        public function updater()
        {
            return $this->belongsTo(User::class, 'updated_by', 'user_id');
        }
}
