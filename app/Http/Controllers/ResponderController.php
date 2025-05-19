<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResponderResource;
use App\Http\Requests\StoreResponderRequest;
use App\Http\Requests\UpdateResponderRequest;
use App\Models\Survey;
use Illuminate\Support\Str;

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

        return new ResponderResource($responder);
    }

}
