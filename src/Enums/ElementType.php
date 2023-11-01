<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Enums;

enum ElementType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case DATETIME = 'datetime';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case MULTIPLE_CHOICE_GRID = 'multiple_choice_grid';
    case SCALE = 'scale';
    case INFO = 'info';
    case CUSTOM = 'custom';
}
