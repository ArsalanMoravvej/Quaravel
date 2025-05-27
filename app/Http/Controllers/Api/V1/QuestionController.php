<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreQuestionRequest;
use App\Http\Requests\V1\UpdateQuestionRequest;
use App\Http\Resources\V1\QuestionResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Survey $survey)
    {
        $questions = $survey->questions->loadMissing('options');
        return QuestionResource::collection($questions);
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
            $options = array_map(
                fn($option, $index) => [
                    'body' => $option['body'],
                    'is_active' => $option['is_active'] ?? true,
                    'question_id' => $question->id,
                    'order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $options,
                array_keys($options)
            );

            QuestionOption::insert($options);
        }

        return new QuestionResource(
            $question
                ->loadMissing('options')
                ->refresh()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey, Question $question): QuestionResource
    {
        return new QuestionResource($question->loadMissing('options'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Survey $survey, Question $question)
    {
        return response()->json($request->validated());
        //TODO
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey, Question $question): JsonResponse
    {
        $question->delete();
        return response()->json(null, 204);
    }
}
