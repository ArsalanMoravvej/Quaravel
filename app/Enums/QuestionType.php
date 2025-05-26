<?php

namespace App\Enums;

enum QuestionType: int
{
    case Text = 1;
    case MultipleChoice = 2;
    case Numeral = 3;
    case OpinionScale = 4;
    case Rating = 5;
    case DropDown = 6;
    case Ranking = 7;

    // App\Enums\QuestionType.php
    public function hasOptions(): bool
    {
        return in_array($this, [
            self::MultipleChoice,
            self::DropDown,
            self::Ranking,
        ]);
    }
}
