<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReviewContent extends Model
{
    protected $table = 'review_contents';
    protected $primaryKey = 'review_content_id';
    protected $fillable = [
        'review_id',
        'value',
        'created_by',
        'updated_by',
    ];
    public function review_item()
    {
        return $this->belongsTo(ReviewItem::class, 'review_item_id', 'review_item_id')->orderBy('seq');;
    }
    public $timestamps = true;
}