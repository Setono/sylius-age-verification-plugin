<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Tests\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerInterface;
use Setono\SyliusAgeVerificationPlugin\Model\AgeAwareCustomerTrait;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="sylius_customer")
 */
class Customer extends BaseCustomer implements AgeAwareCustomerInterface
{
    use AgeAwareCustomerTrait;
}
