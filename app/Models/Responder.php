<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Responder extends Model
{
    /** @use HasFactory<\Database\Factories\ResponderFactory> */
    use HasFactory;

    protected $guarded = [];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);

    }
}
