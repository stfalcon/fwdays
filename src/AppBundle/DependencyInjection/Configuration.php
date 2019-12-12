<?php

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('application_default');
        $rootNode
            ->children()
                ->scalarNode('payment_system')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('wayforpay')
                    ->children()
                        ->scalarNode('shop_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('interkassa')
                    ->children()
                        ->scalarNode('shop_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->scalarNode('discount')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
