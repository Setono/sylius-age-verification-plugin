<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\UrlGenerator;

use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;

interface AuthorizationUrlGeneratorInterface
{
    public function generateUrl(MinimumAge $age, string $countryCode): string;
}
