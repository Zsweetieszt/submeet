<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicUser extends Model
{
    protected $table = 'topic_users';
    public $incrementing = false;
    protected $primaryKey = ['topic_id', 'user_id'];
    protected $fillable = ['topic_id', 'user_id'];

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}