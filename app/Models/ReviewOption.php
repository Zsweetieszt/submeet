<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewOption extends Model
{
    protected $table = 'review_options';
    protected $primaryKey = 'review_option_id';
    protected $fillable = [
        'review_item_id',
        'scale',
        'desc',
        'created_by',
        'updated_by',
    ];

    public function reviewItem()
    {
        return $this->belongsTo(ReviewItem::class, 'review_item_id', 'review_item_id');
    }

    public $timestamps = true;
}
