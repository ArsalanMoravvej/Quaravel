<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreQuestionRequest;
use App\Http\Requests\V1\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Support\Arr;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request, Survey $survey)
    {
        $data = $request->validated();
        $options = Arr::pull($data, 'options'); // grabs and removes 'options' from $data

        $question = $survey->questions()->create($data);

        if (in_array($question->type, [
            QuestionType::MultipleChoice,
            QuestionType::DropDown,
            QuestionType::Ranking,
        ]))
        {
            foreach ($options as $optionText) {
                $question->options()->create([
                    'body' => $optionText,
                    "is_visible" => true,
                ]);
            }
        }
        return response()->json(
            $question->loadMissing('options')
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        //
    }
}
