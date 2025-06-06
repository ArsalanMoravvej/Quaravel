<?php

namespace App\Http\Resources\V1;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"          => $this->id,
            "public_code" => $this->public_id,
            "title"       => $this->title,
            "language"    => $this->language,
            "active"      => $this->active,
            "questions"   => QuestionResource::collection($this->whenLoaded('questions'))
        ];
    }
}
