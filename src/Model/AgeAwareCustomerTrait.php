<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait AgeAwareCustomerTrait
{
    /** @ORM\Column(type="datetime", nullable=true) */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $ageCheckedAt = null;

    /** @ORM\Column(type="integer", nullable=true, options={"unsigned"=true}) */
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    protected ?int $olderThan = null;

    // todo test this
    public function isOlderThan(int $age): bool
    {
        if ($this->ageCheckedAt === null || $this->olderThan === null) {
            return false;
        }

        if ($age <= $this->olderThan) {
            return true;
        }

        $now = new \DateTimeImmutable();
        $diff = $now->diff($this->ageCheckedAt);

        return $age <= ($this->olderThan + $diff->y);
    }

    public function setAgeCheckedAt(?\DateTimeInterface $isOverAgeCheckedAt): void
    {
        $this->ageCheckedAt = $isOverAgeCheckedAt;
    }

    public function setOlderThan(?int $age): void
    {
        $this->olderThan = $age;
    }
}
