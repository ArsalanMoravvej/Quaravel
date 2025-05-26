<?php

namespace App\Http\Resources\V1;

use App\Enums\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return array_merge(
            $this->getBaseAttributes(),
            $this->getTypeSpecificAttributes(),
            $this->getConditionalAttributes(),
        );
    }

    private function getBaseAttributes(): array
    {
        return [
            'id' => $this->id,
            'survey_id' => $this->survey_id,
            'type' => $this->type->value,
            'type_name' => $this->type->name,
            'title' => $this->title,
            'description' => $this->description,
            'answer_required' => $this->answer_required,
        ];
    }

    private function getTypeSpecificAttributes(): array
    {
        return match ($this->type) {
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

    private function getConditionalAttributes(): array
    {
        // Additional Dynamic Checks
        $attributes = [];

        if ($this->allow_multiple_select && $this->type === QuestionType::MultipleChoice) {
            $attributes['min_selectable_choices'] = $this->min_selectable_choices;
            $attributes['max_selectable_choices'] = $this->max_selectable_choices;
        }

        return $attributes;
    }


    public function getTextAttributes(): array
    {
        return [
            'answer_min_length' => $this->answer_min_length,
            'answer_max_length' => $this->answer_max_length,
            'placeholder' => $this->when($this->placeholder, $this->placeholder),
        ];
    }


    public function getMultipleChoiceAttributes(): array
    {
        return [
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
            'randomized' => $this->randomized,
            'allow_multiple_select' => $this->allow_multiple_select,
        ];
    }


    public function getNumeralAttributes(): array
    {
        return [
            'allow_decimals' => $this->when(isset($this->allow_decimals), $this->allow_decimals),
            'number_min_value' => when($this->allow_decimals, $this->number_min_value, (int) $this->number_min_value),
            'number_max_value' => when($this->allow_decimals, $this->number_max_value, (int)$this->number_max_value),
        ];
    }


    public function getOpinionScaleAttributes(): array
    {
        return [
            'steps' => $this->steps,
            'start_from_zero' => $this->start_from_zero,
            'negative_scale' => $this->negative_scale,
            'left_label' => $this->when($this->left_label, $this->left_label),
            'center_label' => $this->when($this->center_label, $this->center_label),
            'right_label' => $this->when($this->right_label, $this->right_label),
        ];
    }


    public function getRatingAttributes(): array
    {
        return [
            'steps' => $this->steps,
        ];
    }


    public function getDropDownAttributes(): array
    {
        return [
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
            'randomized' => $this->randomized,
            'alphabetical_order' => $this->alphabetical_order,
            'placeholder' => $this->when($this->placeholder, $this->placeholder),
            'searchable' => $this->when(isset($this->searchable), $this->searchable),
        ];
    }


    public function getRankingAttributes(): array
    {
        return [
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
