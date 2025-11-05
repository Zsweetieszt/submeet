<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewItem extends Model
{
    protected $table = 'review_items';
    protected $primaryKey = 'review_item_id';
    protected $fillable = [
        'name',
        'desc',
        'weight',
        'seq',
        'event_id',
        'created_by',
        'updated_by',
    ];

    public function options()
    {
        return $this->hasMany(ReviewOption::class, 'review_item_id', 'review_item_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function review_contents()
    {
        return $this->hasMany(ReviewContent::class, 'review_item_id', 'review_item_id');
    }

    public $timestamps = true;
}
