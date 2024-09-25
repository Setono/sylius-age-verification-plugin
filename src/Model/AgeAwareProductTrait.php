<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait AgeAwareProductTrait
{
    /** @ORM\Column(type="smallint", nullable=true, options={"unsigned"=true}) */
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    protected ?int $minimumAge = null;

    public function getMinimumAge(): ?int
    {
        return $this->minimumAge;
    }

    public function setMinimumAge(?int $minimumAge): void
    {
        $this->minimumAge = $minimumAge;
    }
}
