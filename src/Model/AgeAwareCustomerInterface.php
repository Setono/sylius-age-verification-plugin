<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

interface AgeAwareCustomerInterface
{
    public function isOlderThan(int $age): bool;

    public function setAgeCheckedAt(\DateTimeInterface $ageCheckedAt): void;

    public function setOlderThan(?int $age): void;
}
