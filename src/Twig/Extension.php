<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class Extension extends AbstractExtension
{
    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('setono_sylius_age_verification__authorization_url', [Runtime::class, 'authorizationUrl']),
            new TwigFunction('setono_sylius_age_verification__minimum_age_check', [Runtime::class, 'minimumAgeCheck']),
        ];
    }
}
