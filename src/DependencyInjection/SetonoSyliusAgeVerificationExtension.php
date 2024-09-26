<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusAgeVerificationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         *
         * @var array{enabled_countries: list<string>, criipto: array{client_id: string, client_secret: string, verify_domain: string}} $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter('setono_sylius_age_verification.enabled_countries', $config['enabled_countries']);
        $container->setParameter('setono_sylius_age_verification.criipto.client_id', $config['criipto']['client_id']);
        $container->setParameter('setono_sylius_age_verification.criipto.client_secret', $config['criipto']['client_secret']);
        $container->setParameter('setono_sylius_age_verification.criipto.verify_domain', $config['criipto']['verify_domain']);

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('sylius_ui', [
            'events' => [
                'sylius.shop.checkout.complete.before_navigation' => [
                    'blocks' => [
                        'setono_sylius_age_verification__age_verification' => [
                            'template' => '@SetonoSyliusAgeVerificationPlugin/shop/_age_verification.html.twig',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
