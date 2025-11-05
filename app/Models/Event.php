<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'event_id';
    protected $fillable = [
        'event_name',
        'event_shortname',
        'event_desc',
        'event_code',
        'event_logo',
        'event_url',
        'event_organizer',
        'event_date',
        'event_date',
        'country_id',
        'manager_name',
        'manager_contact_email',
        'manager_contact_ct',
        'manager_contact_number',
        'support_name',
        'support_contact_email',
        'support_contact_ct',
        'support_contact_number',
        'treasurer_name',
        'treasurer_contact_email',
        'treasurer_contact_ct',
        'treasurer_contact_number',
        'submission_start',
        'submission_end',
        'revision_start',
        'revision_end',
        'join_np_start',
        'join_np_end',
        'camera_ready_start',
        'camera_ready_end',
        'supporting_materials_start',
        'supporting_materials_end',
        'payment_start',
        'payment_end',
        'event_start',
        'event_end',
        'event_status',
        'event_tz',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function user_events()
    {
        return $this->hasMany(UserEvent::class, 'event_id', 'event_id');
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function papers()
    {
        return $this->hasMany(Paper::class, 'event_id', 'event_id');
    }
    public $timestamps = true;

}
