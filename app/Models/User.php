<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as BaseMustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, BaseMustVerifyEmail {
        BaseMustVerifyEmail::markEmailAsVerified insteadof Notifiable;
    }
    protected $table = 'users';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'password',
        'email',
        'given_name',
        'family_name',
        'honorif',
        'institution_name',
        'country_id',
        'ct_phone_number_1',
        'phone_number_1',
        'ct_phone_number_2',
        'phone_number_2',
        'root',
        'status',
        'activated_at',
        'created_by',
        'updated_by',
        'first_login_at',
        'last_login_at'
    ];

    protected $dates = ['activated_at'];

     public function hasVerifiedEmail()
    {
        return !is_null($this->activated_at);
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill(['activated_at' => now()])->save();
    }

    protected $casts = [
        'root' => 'boolean',
        'status' => 'boolean',
        'activated_at' => 'datetime',
        'first_login_at' => 'datetime',
        'last_login_at' => 'datetime'
    ];

    public $timestamps = true;

    public function country()
    {
        return $this->hasOne(Country::class, 'country_id', 'country_id');
    }

    public function paper()
    {
        return $this->hasMany(Paper::class, 'user_id', 'user_id');
    }

    public function user_events()
    {
        return $this->hasMany(UserEvent::class, 'user_id', 'user_id');
    }

    public function topicusers()
    {
        return $this->hasMany(TopicUser::class, 'user_id', 'user_id');
    }

    public function expertise_users()
    {
        return $this->hasMany(ExpertiseUser::class, 'user_id', 'user_id');
    }
}
