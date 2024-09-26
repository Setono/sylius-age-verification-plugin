<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait AgeAwareCustomerTrait
{
    /** @ORM\Column(type="datetime", nullable=true) */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $isOverAgeCheckedAt = null;

    /** @ORM\Column(type="integer", nullable=true, options={"unsigned"=true}) */
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    protected ?int $isOver = null;

    // todo test this
    public function isOver(int $age): bool
    {
        if ($this->isOverAgeCheckedAt === null || $this->isOver === null) {
            return false;
        }

        if ($age <= $this->isOver) {
            return true;
        }

        $now = new \DateTimeImmutable();
        $diff = $now->diff($this->isOverAgeCheckedAt);

        return $age <= ($this->isOver + $diff->y);
    }

    public function setIsOverAgeCheckedAt(?\DateTimeInterface $isOverAgeCheckedAt): void
    {
        $this->isOverAgeCheckedAt = $isOverAgeCheckedAt;
    }

    public function setIsOver(?int $age): void
    {
        $this->isOver = $age;
    }
}
