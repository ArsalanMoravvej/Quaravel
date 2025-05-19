<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasPublicId
{
    public static function bootHasPublicId() : void
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = static::generateUniquePublicId();
            }
        });
    }

    protected static function generateUniquePublicId() : string
    {
        $length = property_exists(static::class, 'publicIdLength') ? static::$publicIdLength : 4;

        do {
            $id = Str::random($length);
        } while (static::where('public_id', $id)->exists());

        return $id;
    }
}

