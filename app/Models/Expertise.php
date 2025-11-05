<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    protected $primaryKey = 'expertise_id';
    public $timestamps = true;

    protected $fillable = [
        'expertise_name',
    ];

    protected $table = 'expertises';

    public function users()
    {
        return $this->hasMany(User::class, 'user_expertise', 'expertise_id', 'user_id');
    }
}
