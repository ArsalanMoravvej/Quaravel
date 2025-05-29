<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreQuestionRequest;
use App\Http\Requests\V1\UpdateQuestionRequest;
use App\Http\Resources\V1\QuestionResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

        $validatedData = $request->validated();

        // grab and remove 'options' from $validatedData
        $options = Arr::pull($validatedData, 'options');

        // Creating the question
        $question = $survey->questions()->create($validatedData);

        // Handling creating the options
        if ($question->type->hasOptions() && $options) {
            $this->handleOptionsStore($question->id, $options);
        }

        // Returning Data Resource
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
    public function update(UpdateQuestionRequest $request, Survey $survey, Question $question): QuestionResource
    {
        $validatedData = $request->validated();

        // grab and remove 'options' from $validatedData
        $newOptions = Arr::pull($validatedData, 'options');

        DB::transaction(function () use ($question, $validatedData, $newOptions) {

            // Updating the question
            $question->update($validatedData);

            // Handling updating the options
            if ($question->type->hasOptions() && $newOptions){
                $this->handleOptionsUpdate($newOptions, $question);
            }
        });
        return new QuestionResource(
            $question
                ->loadMissing('options')
                ->refresh()
        );

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey, Question $question): JsonResponse
    {
        $question->delete();
        return response()->json(null, 204);
    }

    /**
     * Helper function for handling storing the options
     */
    private function handleOptionsStore(int $question_id, mixed $options): void
    {
        // We can use ==> $question->options()->createMany($options); <== but it has a problem of inserting
        // with an insert query for each option and not all at once (because of the timestamps columns) so
        // we try importing the manually by ==> QuestionOption::insert($options); <== with manually adding
        // timestamps and question_id (since it no longer benefits from the eloquent relationship).

        $now = now();
        $options = array_map(
            fn($option, $index) => [
                'body' => $option['body'],
                'is_active' => $option['is_active'] ?? true,
                'question_id' => $question_id,
                'order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $options,
            array_keys($options)
        );

        QuestionOption::insert($options);
    }

    /**
     * Helper function for handling updating the options
     */
    private function handleOptionsUpdate(mixed $newOptions, Question $question): void
    {
        $existingOptionIds = array_filter(Arr::pluck($newOptions, 'id'));

        // Soft delete options not in the new list
        if (!empty($existingOptionIds)) {
            $question->options()
                ->whereNotIn('id', $existingOptionIds)
                ->delete(); // Soft delete
        }

        foreach ($newOptions as $order => $optionData) {

            // Common fields
            $optionFields = [
                'body' => $optionData['body'],
                'is_active' => $optionData['is_active'],
                'order' => $order,
            ];

            if (!empty($optionData['id'])) {
                // Update existing
                $question->options()->where('id', $optionData['id'])->update($optionFields);
            } else {
                // Create new
                $question->options()->create($optionFields);
            }
        }
    }
}
