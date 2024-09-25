<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareProductInterface;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct implements AgeAwareProductInterface
{
    use AgeAwareProductTrait;
}
