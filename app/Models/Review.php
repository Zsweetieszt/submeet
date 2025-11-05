<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    protected $fillable = [
        'assign_id',
        'note_for_author',
        'note_for_editor',
        'attach_file',
        'attach_url',
        'recommendation'
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assign_id', 'assign_id');
    }

    public function review_contents()
    {
        return $this->hasMany(ReviewContent::class, 'review_id', 'review_id');
    }

    public $timestamps = true;
}
