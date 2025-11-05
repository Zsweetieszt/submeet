<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    protected $table = 'decisions';
    protected $primaryKey = 'decision_id';
    public $timestamps = true;

    protected $fillable = [
        'first_paper_sub_id',
        'last_paper_sub_id',
        'paper_sub_id',
        'decision',
        'editor_id',
        'note_for_author',
        'created_by',
        'updated_by',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_sub_id', 'paper_sub_id');
    }
    public function firstPaper()
    {
        return $this->belongsTo(Paper::class, 'first_paper_sub_id', 'paper_sub_id');
    }
    public function lastPaper()
    {
        return $this->belongsTo(Paper::class, 'last_paper_sub_id', 'paper_sub_id');
    }
    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id', 'user_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}
