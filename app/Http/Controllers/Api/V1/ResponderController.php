<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ResponderResource;
use App\Models\Survey;

class ResponderController extends Controller
{
    /**
     *
     */
    public function generate(Survey $survey): ResponderResource
    {
        $responder = $survey->responders()->create([
            'type' => 'Normal'
        ]);

        return new ResponderResource($responder->loadMissing("survey.questions.options"));
    }

}
