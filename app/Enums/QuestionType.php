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
//            self::Text=> 'something',
//            self::MultipleChoice=> 'something1',
//            self::Statement=> 'something2',
//            self::OpinionScale=> 'something3',
//            self::Rating=> 'something4',
//            self::Group=> 'something5',
//            self::DropDown=> 'something6',
//            self::Ranking=> 'something7',
//            self::Matrix=> 'something8',
//        };
//    }
}
