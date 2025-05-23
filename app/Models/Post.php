<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_url',
        'scheduled_time',
        'status',
        'user_id'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class)
            ->withPivot('platform_status')
            ->withTimestamps();
    }

    public function getScheduledTimeAttribute($value)
    {
        $userTimezone = Auth::user()?->timezone;

        return Carbon::parse($value)->setTimezone($userTimezone);
    }

    public function setScheduledTimeAttribute($value)
    {
        $userTimezone = Auth::user()?->timezone;

        $this->attributes['scheduled_time'] = Carbon::parse($value, $userTimezone)->setTimezone('UTC');
    }

}
