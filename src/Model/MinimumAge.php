<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

enum MinimumAge: int
{
    case MINIMUM_15 = 15;
    case MINIMUM_16 = 16;
    case MINIMUM_18 = 18;
    case MINIMUM_21 = 21;

    public function asScope(): string
    {
        return 'is_over_' . $this->value;
    }
}
