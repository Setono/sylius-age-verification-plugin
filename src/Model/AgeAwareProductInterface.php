<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

interface AgeAwareProductInterface
{
    /**
     * Null means no age verification is required
     */
    public function getMinimumAge(): ?int;

    public function setMinimumAge(?int $minimumAge): void;
}
