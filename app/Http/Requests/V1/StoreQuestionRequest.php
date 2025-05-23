<?php

namespace App\Http\Requests\V1;

use App\Enums\QuestionType;
use App\Rules\ChoicesCountLessThanOptionsCount;
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
//        $this->dd($this->input('options', []));
        $baseRules = [
            'type' => ['required', new Enum(QuestionType::class)],
            'title' => ['required', 'string', 'max:255'],
            'answer_required' => ['sometimes', 'boolean'],
        ];

        return array_merge($baseRules, match ($type) {
            QuestionType::Text->value => [
                'answer_min_length' => ['required','integer', 'min:0'],
                'answer_max_length' => ['required', 'integer', 'gte:answer_min_length', 'max:5000'],
            ],

            QuestionType::MultipleChoice->value => [
                'options' => ['required', 'array', 'min:2', 'max:50'],
                'options.*' => ['array'],
                'options.*.body' => ['required', 'string', 'distinct', 'min:1', 'max:50'],
                'options.*.is_visible' => ['required', 'boolean'],
                'randomized' => ['required', 'boolean'],
                'allow_multiple_select' => ['required', 'boolean'],
                'min_selectable_choices' => [
                    'missing_if:allow_multiple_select,false',
                    'required_if:allow_multiple_select,true',
                    'integer',
                    'min:0',
                    new ChoicesCountLessThanOptionsCount($this->input('options', []))
                ],
                'max_selectable_choices' => [
                    'missing_if:allow_multiple_select,false',
                    'required_if:allow_multiple_select,true',
                    'integer',
                    'gte:min_selectable_choices',
                    new ChoicesCountLessThanOptionsCount($this->input('options', []))
                ],
            ],

            QuestionType::Numeral->value => [
                'number_min_value' => ['required', 'decimal:0,3'],
                'number_max_value' => ['required', 'decimal:0,3', 'gt:number_min_value'],
            ],

            QuestionType::OpinionScale->value => [
                'steps' => ['required', 'integer', 'min:3', 'max:11'],
                'start_from_zero' => ['required', 'bool'],
            ],

            QuestionType::Rating->value => [
                'steps' => ['required', 'integer', 'min:2', 'max:10'],
            ],

            QuestionType::DropDown->value => [
                'options' => ['required', 'array', 'min:2', 'max:50'],
                'options.*' => ['array'],
                'options.*.body' => ['required', 'string', 'distinct', 'min:1', 'max:50'],
                'options.*.is_visible' => ['required', 'boolean'],
                'alphabetical_order' => ['required', 'declined_if:randomized,true', 'boolean'],
                'randomized' => ['required', 'declined_if:alphabetical_order,true', 'boolean']
            ],

            QuestionType::Ranking->value => [
                'options' => ['required', 'array', 'min:2', 'max:50'],
                'options.*' => ['array'],
                'options.*.body' => ['required', 'string', 'distinct', 'min:1', 'max:50'],
                'options.*.is_visible' => ['required', 'boolean'],
            ],

            default => []
        });
    }

    public function messages()
    {
        return [
            'options.*.body.distinct' => 'Each option must be unique within the question.',
            'randomized.declined_if' => 'The alphabetical order and randomized fields must not be true at the same time.',
            'alphabetical_order.declined_if' => 'The alphabetical order and randomized fields must not be true at the same time.',
        ];
    }

}
