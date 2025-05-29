<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                ],

            'access_token' => $this->when(isset($this->access_token), $this->access_token),
            'token_type' => $this->when(isset($this->access_token), 'bearer'),
            'expires_in' => $this->when(isset($this->access_token), $this->expires_in),
        ];
    }
}
