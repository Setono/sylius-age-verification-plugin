<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\Form\Extension;

use Setono\SyliusAgeVerificationPlugin\Model\MinimumAge;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cases = array_map(static fn (MinimumAge $minimumAge) => $minimumAge->value, MinimumAge::cases());

        $builder->add('minimumAge', ChoiceType::class, [
            'choices' => array_combine($cases, $cases),
            'label' => 'setono_sylius_age_verification.form.product.minimum_age',
            'help' => 'setono_sylius_age_verification.form.product.minimum_age_help',
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): \Generator
    {
        yield ProductType::class;
    }
}
