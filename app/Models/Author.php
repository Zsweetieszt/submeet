<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'authors';
    protected $primaryKey = 'paper_author_id';
    protected $fillable = [
        'paper_sub_id',
        'email',
        'given_name',
        'family_name',
        'user_id',
        'order',
        'is_corresponding',
        'activated_at',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;
}
