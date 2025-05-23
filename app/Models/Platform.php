<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Platform extends Model
{
    protected $fillable = ['name', 'type'];
    
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
            ->withPivot('platform_status')
            ->withTimestamps();
    }
}
