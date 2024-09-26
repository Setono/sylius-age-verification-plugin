<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration;

interface OpenIdConfigurationFactoryInterface
{
    public function create(): OpenIdConfiguration;
}
