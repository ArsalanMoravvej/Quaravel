<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\RatingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function options(): hasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

//    This might be better off with responses Table!!!!
//    public function text(): hasMany
//    {
//        return $this->hasMany(TextAnswer::class);
//    }

    protected $casts = [
        'type' => QuestionType::class,
        'rating_type' => RatingType::class,
        'answer_required' => 'boolean',
        'randomized' => 'boolean',
        'allow_multiple_select' => 'boolean',
        'alphabetical_order' => 'boolean',
        'allow_decimals' => 'boolean',
        'allow_tied' => 'boolean',
        'start_from_zero' => 'boolean',
        'negative_scale' => 'boolean',
        'number_min_value' => 'float',
        'number_max_value' => 'float',
    ];
}
