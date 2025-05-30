<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreSurveyRequest;
use App\Http\Requests\V1\UpdateSurveyRequest;
use App\Http\Resources\V1\SurveyCollection;
use App\Http\Resources\V1\SurveyResource;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    /**
     * Display a listing of the surveys.
     */
    public function index(): SurveyCollection
    {
        return new SurveyCollection(request()->user()->surveys);
    }

    /**
     * Store a newly created survey in storage.
     */
    public function store(StoreSurveyRequest $request): SurveyResource
    {
        $user = auth()->user();
        $survey = $user->surveys()->create($request->validated());
        return new SurveyResource($survey->refresh());
    }

    /**
     * Display the specified survey.
     */
    public function show(Survey $survey): SurveyResource
    {
        return new SurveyResource($survey->loadMissing("questions.options"));
    }

    /**
     * Update the specified survey in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        $survey->update($request->validated());
        return new SurveyResource($survey);
    }

    /**
     * Remove the specified survey from storage.
     */
    public function destroy(Survey $survey): JsonResponse
    {
        $survey->delete();
        return response()->json(null, 204);
    }
}
