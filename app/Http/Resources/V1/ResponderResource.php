<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'survey' => new SurveyResource($this->survey),
            'responder' => [
                'responder_code'    => $this->public_id,
                'started_date_time' => $this->created_at,
            ],
        ];
    }
}
