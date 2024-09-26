<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration;

/**
 * @final
 *
 * This is non-final because we define it as a lazy service
 */
class OpenIdConfiguration
{
    public function __construct(public readonly string $authorizationEndpoint, public readonly string $tokenEndpoint)
    {
    }
}
