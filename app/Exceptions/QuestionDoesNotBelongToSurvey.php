<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;

class QuestionDoesNotBelongToSurvey extends AuthorizationException
{
    public function __construct()
    {
        parent::__construct('This question does not belong to the specified survey.');
    }
}
