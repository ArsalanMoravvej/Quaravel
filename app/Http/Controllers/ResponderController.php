<?php

namespace App\Http\Controllers;

use App\Models\Responder;
use App\Http\Requests\StoreResponderRequest;
use App\Http\Requests\UpdateResponderRequest;
use App\Models\Survey;
use Illuminate\Support\Str;

class ResponderController extends Controller
{
    /**
     *
     */
    public function generate(Survey $survey)
    {
        $responder = $survey->responders()->create([
            'public_id' => Str::random(6),
            'type' => 'Normal'
        ]);

        return $responder;
    }

}
