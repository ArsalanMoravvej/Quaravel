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
        //Values for Dynamic Validation Checking
        $type = $this->input('type');
        $type = is_numeric($type) ? QuestionType::tryFrom((int) $type) : null;
        $allowDecimals = $this->input('allow_decimals');
        $options = $this->input('options', []);

        //Base Rules
        $baseRules = [
            'type' => ['required', new Enum(QuestionType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'alphabetical_order' => ['sometimes', 'declined_if:randomized,true', 'boolean'],
            'randomized' => ['sometimes', 'declined_if:alphabetical_order,true', 'boolean'],
            'answer_required' => ['sometimes', 'boolean'],
        ];

        // Question type Specific Validations
        return array_merge($baseRules, match ($type) {
            QuestionType::Text => [
                'answer_min_length' => ['required','integer', 'min:0'],
                'answer_max_length' => ['required', 'integer', 'gte:answer_min_length', 'max:5000'],
            ],

            QuestionType::MultipleChoice,
            QuestionType::DropDown,
            QuestionType::Ranking
            => [
                'options' => ['required', 'array', 'min:2', 'max:50'],
                'options.*' => ['array'],
                'options.*.body' => ['required', 'string', 'distinct', 'min:1', 'max:50'],
                'options.*.is_visible' => ['required', 'boolean'],
                ...($type===QuestionType::MultipleChoice ? $this->multipleChoiceRules($options) : [])
            ],

            QuestionType::Numeral => [
                'allow_decimals' => ['required', 'boolean'],
                'number_min_value' => [
                    'required',
                    $allowDecimals ? 'decimal:0,3' : 'integer',
                    'min:-10000'
                ],
                'number_max_value' => [
                    'required',
                    $allowDecimals ? 'decimal:0,3' : 'integer',
                    'gt:number_min_value',
                    'max:10000'
                ],
            ],

            QuestionType::OpinionScale => [
                'steps' => ['required', 'integer', 'min:3', 'max:11'],
                'start_from_zero' => ['sometimes', 'declined_if:negative_scale,true', 'boolean'],
                'negative_scale' => ['sometimes', 'declined_if:start_from_zero,true', 'boolean'],
                'left_label' => ['sometimes', 'string', 'min:1', 'max:50'],
                'center_label' => ['sometimes', 'string', 'min:1', 'max:50'],
                'right_label' => ['sometimes', 'string', 'min:1', 'max:50'],
            ],

            QuestionType::Rating => [
                'steps' => ['required', 'integer', 'min:2', 'max:10'],
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
            'min_selectable_choices.lte' => 'The min selectable choices field must be less than or equal to max selectable choices.',
            'max_selectable_choices.gte' => 'The max selectable choices field must be greater than or equal to min selectable choices.',
            'min_selectable_choices.missing_unless' => 'The min selectable choices field must be missing unless allow multiple select is present and true.',
            'max_selectable_choices.missing_unless' => 'The max selectable choices field must be missing unless allow multiple select is present and true.'
        ];
    }

    private function multipleChoiceRules(array $options): array
    {
        return [
            'allow_multiple_select' => [
                'boolean',
                'required_with_all:min_selectable_choices,max_selectable_choices',
            ],
            'min_selectable_choices' => [
                'required_if:allow_multiple_select,true',
                'missing_unless:allow_multiple_select,true',
                'exclude_without:max_selectable_choices',
                'integer',
                'min:0',
                'lte:max_selectable_choices',
                new ChoicesCountLessThanOptionsCount($options),
            ],
            'max_selectable_choices' => [
                'required_if:allow_multiple_select,true',
                'missing_unless:allow_multiple_select,true',
                'exclude_without:min_selectable_choices',
                'integer',
                'gte:min_selectable_choices',
                new ChoicesCountLessThanOptionsCount($options),
            ],
        ];
    }


}
