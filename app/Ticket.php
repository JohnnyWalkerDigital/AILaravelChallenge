<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
