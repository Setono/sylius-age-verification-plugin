<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

interface AgeAwareCustomerInterface
{
    public function isOver(int $age): bool;

    public function setIsOverAgeCheckedAt(\DateTimeInterface $ageCheckedAt): void;

    public function setIsOver(int $age): void;
}
