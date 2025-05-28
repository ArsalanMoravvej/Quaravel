<?php

namespace App\Http\Requests\V1;

use App\Enums\QuestionType;
use App\Enums\RatingType;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateQuestionRequest extends FormRequest
{
    private mixed $question;

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
     * @throws ValidationException
     */
    public function rules(): array
    {
        $this->question = $this->route('question');

        $this->assertQuestionTypeIsUnchanged();

        return array_merge(
            $this->getBaseAttributes(),
            $this->getTypeSpecificAttributes($this->question->type),
            $this->getConditionalAttributes(),
        );
    }

    private function getBaseAttributes(): array
    {
        return [
            'type' => ['sometimes', new Enum(QuestionType::class)],
            'title' => ['sometimes', 'string', 'max:255'],
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
            'answer_min_length' => ['sometimes', 'integer', 'min:0'],
            'answer_max_length' => ['sometimes', 'integer', 'max:5000'],
            'placeholder' => ['sometimes', 'string', 'max:100'],
        ];
    }

    private function getMultipleChoiceAttributes(): array
    {
        return array_merge(
            $this->optionsBasedRules(),
            $this->multipleSelectRules(),
            [
                // Additional Rules
                'alphabetical_order' => ['sometimes', 'declined_if:randomized,true', 'boolean'],
            ]
        );
    }

    private function getNumeralAttributes(): array
    {
        $allowDecimals = filter_var(
            $this->input('allow_decimals', $this->question->allow_decimals),
            FILTER_VALIDATE_BOOL
        );

        $dataType = $allowDecimals ? 'decimal:0,3' : 'integer';

        return [
            'allow_decimals' => ['sometimes', 'boolean'],
            'number_min_value' => [
                $dataType,
                'min:-10000'
            ],
            'number_max_value' => [
                $dataType,
                'max:10000'
            ],
        ];
    }

    private function getOpinionScaleAttributes(): array
    {
        return [
            'steps' => ['sometimes', 'integer', 'min:3', 'max:11'],
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
            'steps' => ['sometimes', 'integer', 'min:2', 'max:10'],
            'rating_type' => ['sometimes', new Enum(RatingType::class)],
        ];
    }

    private function getDropDownAttributes(): array
    {
        return array_merge(
            $this->optionsBasedRules(),
            [
                // Additional Rules
                'placeholder' => ['sometimes', 'string', 'max:100'],
                'alphabetical_order' => ['sometimes', 'declined_if:randomized,true', 'boolean'],
            ]
        );
    }

    private function getRankingAttributes(): array
    {
        return array_merge(
            $this->optionsBasedRules(),
            [
                // Additional Rules
                'allow_tied' => ['sometimes', 'boolean'],
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
            'options' => ['sometimes', 'array', 'min:2', 'max:50'],
            'options.*' => ['array'],
            'options.*.id' => [
                'sometimes', 'integer', 'distinct', 'min:1',
                Rule::exists('question_options', 'id')->where('question_id', $this->question->id),
                ],
            'options.*.body' => ['required_with:options', 'string', 'distinct', 'min:1', 'max:50'],
            'options.*.is_active' => ['required_with:options', 'boolean'],
            'randomized' => ['sometimes', 'declined_if:alphabetical_order,true', 'boolean'],
        ];
    }

    private function multipleSelectRules(): array
    {
        return [
            'allow_multiple_select' => ['sometimes', 'boolean'],
            'min_selectable_choices' => ['sometimes', 'integer', 'min:0'],
            'max_selectable_choices' => ['sometimes', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.*.body.distinct' => 'Each option must be unique within the question.',
            'randomized.declined_if' => 'The “alphabetical” order and “randomized” fields must not be true at the same time.',
            'alphabetical_order.declined_if' => 'The “alphabetical” order and “randomized” fields must not be true at the same time.',
            'options.*.body.required_with' => 'option “body” must be present when editing.',
            'options.*.is_active' => 'option “is_active” state must be present when editing.',
        ];
    }

    /**
     * @return void
     * @throws ValidationException
     */
    public function assertQuestionTypeIsUnchanged(): void
    {
        $typeInRequest = $this->input('type');
        $typeInRequest = is_numeric($typeInRequest) ? QuestionType::tryFrom((int)$typeInRequest) : null;
        if ($typeInRequest !== null && $typeInRequest !== $this->question->type) throw ValidationException::withMessages([
            'type' => 'The type of the question is immutable. If you need another question type, please consider creating a new question instead.',
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

                match ($this->question->type) {
                QuestionType::Text => $this->TextCheck($validator),
                QuestionType::MultipleChoice => $this->multipleChoiceCheck($validator),
                QuestionType::Numeral => $this->numeralCheck($validator),

                default => []
            };

        });
    }

    /**
     * @param $validator
     * @return void
     */
    private function multipleChoiceCheck($validator): void
    {
        $options =    $this->input('options', $this->question->options->toArray());
        $minChoices = $this->input('min_selectable_choices', $this->question->min_selectable_choices);
        $maxChoices = $this->input('max_selectable_choices', $this->question->max_selectable_choices);

        if ($minChoices > $maxChoices) {
            $validator->errors()->add('min_selectable_choices', 'The min_selectable_choice field must not be greater than the max_selectable_choice field.');
        }

        if ($minChoices > count($options)) {
            $validator->errors()->add('min_selectable_choices', 'The min_selectable_choice field must not be greater than the number of options.');
        }

        if ($maxChoices > count($options)) {
            $validator->errors()->add('max_selectable_choices', 'The max_selectable_choice field must not be greater than the number of options.');
        }

    }

    /**
     * @param $validator
     * @return void
     */
    private function numeralCheck($validator): void
    {
        $min = $this->input('number_min_value', $this->question->number_min_value);
        $max = $this->input('number_max_value', $this->question->number_max_value);

        if ($min > $max) {
            $validator->errors()->add('number_value', 'The number_min_value must be less than the number_max_value.');
        }
    }
    private function TextCheck($validator): void
    {
        $min = $this->input('answer_min_length', $this->question->answer_min_length);
        $max = $this->input('answer_max_length', $this->question->answer_max_length);

        if ($min > $max) {
            $validator->errors()->add('number_value', 'The answer_min_length must be less than the answer_max_length.');
        }
    }
}
