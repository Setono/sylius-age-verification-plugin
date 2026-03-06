<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

enum MinimumAge: int
{
    case MINIMUM_16 = 16;
    case MINIMUM_18 = 18;
}
