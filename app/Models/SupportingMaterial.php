<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportingMaterial extends Model
{
    protected $table = 'supporting_materials';
    protected $primaryKey = 'supp_material_id';
    public $timestamps = true;

    protected $fillable = [
        'slide_file',
        'poster_file',
        'video_url',
        'first_paper_sub_id',
        'event_id',
        'created_by',
        'updated_by',
    ];
}
