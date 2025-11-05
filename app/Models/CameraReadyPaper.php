<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CameraReadyPaper extends Model
{
    protected $table = 'camera_ready_papers';
    protected $primaryKey = 'camera_ready_id';
    public $timestamps = true;

    protected $fillable = [
        'cr_paper_file',
        'copyright_trf_file',
        'first_paper_sub_id',
        'event_id',
        'created_by',
        'updated_by',
    ];
}
