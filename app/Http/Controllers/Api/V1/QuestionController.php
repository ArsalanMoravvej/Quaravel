<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreQuestionRequest;
use App\Http\Requests\V1\UpdateQuestionRequest;
use App\Http\Resources\V1\QuestionResource;
use App\Models\Question;
use App\Models\QuestionOption;
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
    public function store(StoreQuestionRequest $request, Survey $survey): QuestionResource
    {
        // Later we might ship this to a delegated service.

        $data = $request->validated();
        $options = Arr::pull($data, 'options'); // grabs and removes 'options' from $data

        $question = $survey->questions()->create($data);

        if ($question->type->hasOptions())
        {
            // We can use ==> $question->options()->createMany($options); <== but it has a problem of inserting
            // with an insert query for each option and not all at once (because of the timestamps columns) so
            // we try importing the manually by ==> QuestionOption::insert($options); <== with manually adding
            // timestamps and question_id (since it no longer benefits from the eloquent relationship.
            $now = now();

            $options = array_map(fn($option) => [
                ...$option,
                'question_id' => $question->id,
                'created_at' => $now,
                'updated_at' => $now,
            ], $options);

            QuestionOption::insert($options);
        }
        return new QuestionResource(
            $question
                ->refresh()
                ->loadMissing('options')
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
