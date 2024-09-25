<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('minimumAge', IntegerType::class, [
            'label' => 'setono_sylius_age_verification.form.product.minimum_age',
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): \Generator
    {
        yield ProductType::class;
    }
}
