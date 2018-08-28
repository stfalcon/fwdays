<?php

namespace Stfalcon\Bundle\EventBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('stfalcon_event');

        $rootNode
            ->children()
                ->scalarNode('discount')->end()
                ->arrayNode('interkassa')
                    ->children()
                        ->scalarNode('shop_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('wayforpay')
                    ->children()
                        ->scalarNode('shop_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
