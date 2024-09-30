<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Token;

use Setono\SyliusAgeVerificationPlugin\OpenIdConfiguration\OpenIdConfiguration;

interface TokenDecoderInterface
{
    public function decode(string $token, OpenIdConfiguration $openIdConfiguration): DecodedToken;
}
