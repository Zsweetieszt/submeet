<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'assignments';
    protected $primaryKey = 'assign_id';
    protected $fillable = [
        'reviewer_id',
        'paper_sub_id',
        'first_paper_sub_id',
        'order',
        'assigned_by'
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'first_paper_sub_id', 'paper_sub_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'assign_id', 'assign_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }

    public $timestamps = true;
}
