<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image',
        'scheduled_time',
        'published_at',
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


}
