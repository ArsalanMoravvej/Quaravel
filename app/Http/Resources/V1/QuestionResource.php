<?php

namespace App\Http\Resources\V1;

use App\Enums\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = [
            'id' => $this->id,
            'survey_id' => $this->survey_id,
            'title' => $this->title,
            'type' => $this->type->name,
            'answer_required' => $this->answer_required,
        ];

        $typeSpecific = match ($this->type) {
            QuestionType::Text => [
                'answer_min_length' => $this->answer_min_length,
                'answer_max_length' => $this->answer_max_length,
            ],

            QuestionType::MultipleChoice => [
                'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
                'randomized' => $this->randomized,
                'allow_multiple_select' => $this->allow_multiple_select,
            ],

            QuestionType::Numeral => [
                'number_min_value' => $this->number_min_value,
                'number_max_value' => $this->number_max_value,
            ],

            QuestionType::OpinionScale => [
                'steps' => $this->steps,
                'start_from_zero' => $this->start_from_zero,
            ],

            QuestionType::Rating => [
                'steps' => $this->steps,
            ],

            QuestionType::DropDown => [
                'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
                'randomized' => $this->randomized,
                'alphabetical_order' => $this->alphabetical_order,
            ],

            QuestionType::Ranking => [
                'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
            ],

            default => []
        };

        // Additional Checks
        if ($this->allow_multiple_select) {
            $typeSpecific['min_selectable_choices'] = $this->min_selectable_choices;
            $typeSpecific['max_selectable_choices'] = $this->max_selectable_choices;
        }

        return array_merge($base, $typeSpecific);
    }
}
