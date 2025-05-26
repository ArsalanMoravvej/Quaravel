<?php

namespace App\Http\Requests\V1;

use App\Enums\QuestionType;
use App\Enums\RatingType;
use App\Rules\ChoicesCountLessThanOptionsCount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreQuestionRequest extends FormRequest
{
    private QuestionType $type;
    private array $options;
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
        $this->type = is_numeric($type) ? QuestionType::tryFrom((int) $type) : null;
        $this->options = $this->input('options', []);

        return array_merge(
            $this->getBaseAttributes(),
            $this->getTypeSpecificAttributes($this->type),
            $this->getConditionalAttributes(),
        );
    }

    private function getBaseAttributes(): array
    {
        return [
            'type' => ['required', new Enum(QuestionType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'answer_required' => ['sometimes', 'boolean'],
        ];
    }

    private function getTypeSpecificAttributes($type): array
    {
        return match ($type) {
            QuestionType::Text => $this->getTextAttributes(),
            QuestionType::MultipleChoice => $this->getMultipleChoiceAttributes(),
            QuestionType::Numeral => $this->getNumeralAttributes(),
            QuestionType::OpinionScale => $this->getOpinionScaleAttributes(),
            QuestionType::Rating => $this->getRatingAttributes(),
            QuestionType::DropDown => $this->getDropDownAttributes(),
            QuestionType::Ranking => $this->getRankingAttributes(),

            default => []
        };
    }

    private function getTextAttributes(): array
    {
        return [
            'answer_min_length' => ['required','integer', 'min:0'],
            'answer_max_length' => ['required', 'integer', 'gte:answer_min_length', 'max:5000'],
            'placeholder' => ['sometimes', 'string', 'max:100'],
        ];
    }

    private function getMultipleChoiceAttributes(): array
    {
        return array_merge(
            $this->optionsBasedRules(),
            $this->multipleSelectRules(),
            [
                'alphabetical_order' => ['sometimes', 'declined_if:randomized,true', 'boolean'],
                'randomized' => ['sometimes', 'declined_if:alphabetical_order,true', 'boolean'],
            ]
        );
    }

    private function getNumeralAttributes(): array
    {
        $allowDecimals = $this->input('allow_decimals');
        return [
            'allow_decimals' => ['sometimes', 'boolean'],
            'number_min_value' => [
                'required',
                $allowDecimals ? 'decimal:0,3' : 'integer',
                'lt:number_max_value',
                'min:-10000'
            ],
            'number_max_value' => [
                'required',
                $allowDecimals ? 'decimal:0,3' : 'integer',
                'gt:number_min_value',
                'max:10000'
            ],
        ];
    }

    private function getOpinionScaleAttributes(): array
    {
        return [
            'steps' => ['required', 'integer', 'min:3', 'max:11'],
            'start_from_zero' => ['sometimes', 'declined_if:negative_scale,true', 'boolean'],
            'negative_scale' => ['sometimes', 'declined_if:start_from_zero,true', 'boolean'],
            'left_label' => ['sometimes', 'string', 'min:1', 'max:50'],
            'center_label' => ['sometimes', 'string', 'min:1', 'max:50'],
            'right_label' => ['sometimes', 'string', 'min:1', 'max:50'],
        ];
    }

    private function getRatingAttributes(): array
    {
        return [
            'steps' => ['required', 'integer', 'min:2', 'max:10'],
            'rating_type' => ['sometimes', new Enum(RatingType::class)],
        ];
    }

    private function getDropDownAttributes(): array
    {
        return array_merge(
            $this->optionsBasedRules(),
            [
                'placeholder' => ['sometimes', 'string', 'max:100'],
                'alphabetical_order' => ['sometimes', 'declined_if:randomized,true', 'boolean'],
                'randomized' => ['sometimes', 'declined_if:alphabetical_order,true', 'boolean'],
            ]
        );
    }

    private function getRankingAttributes(): array
    {
        return array_merge(
            $this->optionsBasedRules(),
            [
                'allow_ties' => ['sometimes', 'boolean'],
            ],
        );
    }

    private function getConditionalAttributes(): array
    {
        return [];
    }

    /**
     * Check if question type uses options.
     */
    private function optionsBasedRules(): array
    {
        return [
            'options' => ['required', 'array', 'min:2', 'max:50'],
            'options.*' => ['array'],
            'options.*.body' => ['required', 'string', 'distinct', 'min:1', 'max:50'],
            'options.*.is_visible' => ['required', 'boolean'],
        ];
    }

    private function multipleSelectRules(): array
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
                new ChoicesCountLessThanOptionsCount($this->options),
            ],
            'max_selectable_choices' => [
                'required_if:allow_multiple_select,true',
                'missing_unless:allow_multiple_select,true',
                'exclude_without:min_selectable_choices',
                'integer',
                'gte:min_selectable_choices',
                new ChoicesCountLessThanOptionsCount($this->options),
            ],
        ];
    }

    public function messages(): array
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

}
