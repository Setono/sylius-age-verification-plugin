<?php

declare(strict_types=1);

namespace Setono\SyliusAgeVerificationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_age_verification');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @phpstan-ignore-next-line */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('enabled_countries')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('verify_id')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('plugin_key')
                            ->cannotBeEmpty()
                            ->defaultValue('%env(VERIFYID_PLUGIN_KEY)%')
        ;

        return $treeBuilder;
    }
}
