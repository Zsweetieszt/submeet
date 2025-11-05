<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLogs extends Model
{
    protected $table = 'api_logs';
        
        protected $primaryKey = 'api_log_id';
        
        protected $fillable = [
            'type',
            'response_data'
        ];
        
        protected $casts = [
            'response_data' => 'array'
        ];
        
        public $timestamps = true;
}
