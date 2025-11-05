<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    protected $table = 'paper_submissions';
    protected $primaryKey = 'paper_sub_id';
    protected $fillable = [
        'first_paper_sub_id',
        'event_id',
        'user_id',
        'round',
        'title',
        'subtitle',
        'abstract',
        'authors',
        'corresponding',
        'keywords',
        'attach_file',
        'attach_url',
        'note_for_editor',
        'status',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
    public function assignment()
    {
        return $this->hasMany(Assignment::class, 'paper_sub_id', 'paper_sub_id')->orderBy('order', 'asc');
    }
    public function author()
    {
        return $this->hasMany(Author::class, 'paper_sub_id', 'paper_sub_id');
    }

    public function topicpapers()
    {
        return $this->hasMany(TopicPaper::class, 'first_paper_sub_id', 'paper_sub_id');
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class, 'paper_sub_id', 'paper_sub_id');
    }

    public function cameraReady()
    {
        return $this->hasMany(CameraReadyPaper::class, 'first_paper_sub_id', 'paper_sub_id');
    }

    public function supportingMaterials()
    {
        return $this->hasMany(SupportingMaterial::class, 'first_paper_sub_id', 'paper_sub_id');
    }

    public $timestamps = true;

    public function payment()
    {
        return $this->hasOne(Payment::class, 'first_paper_sub_id', 'paper_sub_id');
    }

    public function first()
    {
        return $this->hasOne(Paper::class, 'paper_sub_id', 'first_paper_sub_id');
    }
}
