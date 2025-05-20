<?php

namespace App\Http\Requests\V1;

use App\Enums\QuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $type = $this->input('type');

        $baseRules = [
            'type' => ['required', new Enum(QuestionType::class)],
            'title' => ['required', 'string', 'max:255'],
        ];

        return array_merge($baseRules, match ($type) {
            QuestionType::Text->value => [
                'min_length' => ['nullable', 'integer'],
                'max_length' => ['nullable', 'integer', 'gt:min_length', 'max:5000'],
            ],

            QuestionType::MultipleChoice->value => [
                'options' => ['required', 'array', 'min:2', 'max:50'],
                'options.*' => ['string', 'min:1'],
            ],

            QuestionType::Numeral->value => [
                'min_value' => ['required', 'decimal:0,3'],
                'max_value' => ['required', 'decimal:0,3', 'gt:min_value'],
            ],

            QuestionType::Rating->value => [
                'min_value' => ['required', 'integer'],
                'max_value' => ['required', 'integer', 'gt:min_value'],
            ],

            default => []
        });
    }
}
