<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\UrlGenerator;

use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AuthorizationUrlGenerator implements AuthorizationUrlGeneratorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function generateUrl(MinimumAge $age): string
    {
        return $this->urlGenerator->generate(
            'setono_sylius_age_verification_shop_initiate',
            ['age' => $age->value],
        );
    }
}
