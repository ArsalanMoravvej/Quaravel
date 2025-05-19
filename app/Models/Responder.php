<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Responder extends Model
{
    /** @use HasFactory<\Database\Factories\ResponderFactory> */
    use HasFactory, HasPublicId;

    protected static int $publicIdLength = 5;

    protected $guarded = [];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
