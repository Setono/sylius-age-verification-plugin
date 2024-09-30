<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Token;

final class DecodedToken
{
    public function __construct(
        public string $iss,
        public string $aud,
        public string $nonce,
        /** @var array{country: string, is_over_15?: bool, is_over_16?: bool, is_over_18?: bool, is_over_21?: bool} $ageVerification */
        public array $ageVerification,
    ) {
    }

    /**
     * Returns the age this user is over or null if the user is not over any age
     */
    public function olderThan(): ?int
    {
        return match (true) {
            $this->ageVerification['is_over_21'] ?? null === true => 21,
            $this->ageVerification['is_over_18'] ?? null === true => 18,
            $this->ageVerification['is_over_16'] ?? null === true => 16,
            $this->ageVerification['is_over_15'] ?? null === true => 15,
            default => null,
        };
    }
}
