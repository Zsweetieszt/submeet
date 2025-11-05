<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $primaryKey = 'topic_id';

    protected $fillable = [
        'topic_name',
        'event_id',
    ];

    public $timestamps = true;

    protected $table = 'topics';

    public function event()
    {
        return $this->hasOne(Event::class, 'event_id', 'event_id');
    }
}
