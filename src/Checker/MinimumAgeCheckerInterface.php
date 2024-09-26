<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Checker;

use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Sylius\Component\Core\Model\OrderInterface;

interface MinimumAgeCheckerInterface
{
    /**
     * Returns null if the order is not age restricted else returns the minimum age required
     */
    public function check(OrderInterface $order): ?MinimumAge;
}
