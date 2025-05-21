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
    // Group and Matrix and Special Variable & ETC...


//    public function label(): string
//    {
//        return match ($this) {
//            self::Text=> 'some label',
//            self::MultipleChoice=> 'some label',
//        };
//    }
}
