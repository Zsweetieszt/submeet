<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertiseUser extends Model
{
    protected $fillable = [
        'expertise_id',
        'user_id',
    ];

    // Disable auto-incrementing primary key
    public $incrementing = false;

    // Define composite primary key
    protected $primaryKey = ['expertise_id', 'user_id'];

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Expertise relationship
    public function expertise()
    {
        return $this->belongsTo(Expertise::class, 'expertise_id', 'expertise_id');
    }
}
