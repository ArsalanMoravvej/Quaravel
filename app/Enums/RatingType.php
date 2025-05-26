<?php

namespace App\Enums;

enum RatingType: int
{
    case Stars = 1;
    case Hearts = 2;
    case ThumbsUps = 3;
    case PileOfPoo = 4;
}
