<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasLabel;

enum CourseLevel: string implements HasLabel
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
    case All = 'all';

    public function getLabel(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Intermediate => 'Intermediate',
            self::Advanced => 'Advanced',
            self::All => 'All Levels',
        };
    }
}
