<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod("PUT")){
            return [
                "title"    => ["required", "string"],
                "language" => ["required", "string", "in:fa,en"],
                "active"   => ["required", "boolean"],
            ];
        }

        // Otherwise if the method is PATCH
        return [
            "title"    => ["sometimes", "string"],
            "language" => ["sometimes", "string", "in:fa,en"],
            "active"   => ["sometimes", "boolean"],
        ];
    }
}
