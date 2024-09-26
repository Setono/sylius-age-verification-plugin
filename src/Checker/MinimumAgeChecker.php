<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Checker;

use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareProductInterface;
use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Sylius\Component\Core\Model\OrderInterface;

final class MinimumAgeChecker implements MinimumAgeCheckerInterface
{
    public function __construct(
        /** @var list<string> $enabledCountries */
        private readonly array $enabledCountries,
    ) {
    }

    public function check(OrderInterface $order): ?MinimumAge
    {
        $countryCode = $order->getShippingAddress()?->getCountryCode();
        if (null === $countryCode || !in_array($countryCode, $this->enabledCountries, true)) {
            return null;
        }

        $minimumAge = null;

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            if (!$product instanceof AgeAwareProductInterface) {
                continue;
            }

            $productMinimumAge = $product->getMinimumAge();

            if (null === $productMinimumAge) {
                continue;
            }

            if (null === $minimumAge || $productMinimumAge > $minimumAge->value) {
                $minimumAge = MinimumAge::from($productMinimumAge);
            }
        }

        return $minimumAge;
    }
}
