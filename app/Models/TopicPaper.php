<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicPaper extends Model
{
    protected $table = 'topic_papers';
    protected $primaryKey = ['topic_id', 'first_paper_sub_id'];
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'topic_id',
        'first_paper_sub_id',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    public function paper()
    {
        return $this->belongsTo(PaperSubmission::class, 'first_paper_sub_id', 'first_paper_sub_id');
    }
}
