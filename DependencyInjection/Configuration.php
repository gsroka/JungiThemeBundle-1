<?php

/*
 * This file is part of the JungiThemeBundle package.
 *
 * (c) Piotr Kugla <piku235@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jungi\Bundle\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The main configuration of the JungiThemeBundle
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jungi_theme');

        $rootNode
            ->children()
                ->arrayNode('holder')
                    ->addDefaultsIfNotSet()
                    ->info('theme holder configuration')
                    ->children()
                        ->scalarNode('id')->defaultValue('jungi_theme.holder.default')->end()
                    ->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return array('id' => $v);
                        })
                    ->end()
                ->end()
                ->append($this->addThemeSelectorNode())
                ->append($this->addThemeResolverNode())
            ->end();

        return $treeBuilder;
    }

    protected function addThemeSelectorNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('selector');

        $rootNode
            ->addDefaultsIfNotSet()
            ->info('theme selector configuration')
            ->children()
                ->booleanNode('ignore_null_themes')
                    ->info('whether to ignore null theme names, when a theme resolver does not return any theme name.')
                    ->defaultTrue()
                ->end()
                ->arrayNode('validation_listener')
                    ->info('theme validation listener configuration')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                    ->children()
                        ->booleanNode('use_investigator')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('device_switch')
                    ->info('device theme switch configuration')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                ->end()
            ->end();

        return $rootNode;
    }

    protected function addThemeResolverNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('resolver');
        $investigatorNorm = function ($v) {
            if (isset($v['suspects'])) {
                array_walk($v['suspects'], function (&$class) {
                    if (false === strpos($class, '\\')) {
                        $class = 'Jungi\\Bundle\\ThemeBundle\\Resolver\\' . $class;
                    }

                    if (!class_exists($class)) {
                        throw new \InvalidArgumentException(sprintf('The theme resolver "%s" can not be found.', $class));
                    }

                    return $class;
                });
            }

            return $v;
        };

        $rootNode
            ->addDefaultsIfNotSet()
            ->isRequired()
            ->info('general theme resolver configuration')
            ->children()
                ->append($this->addFallbackThemeResolverNode())
                ->append($this->addPrimaryThemeResolverNode())
                ->arrayNode('investigator')
                    ->info('theme resolver investigator configuration')
                    ->canBeDisabled()
                    ->fixXmlConfig('suspect')
                    ->children()
                        ->arrayNode('suspects')
                            ->defaultValue(array('Jungi\Bundle\ThemeBundle\Resolver\CookieThemeResolver'))
                            ->prototype('scalar')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                    ->beforeNormalization()
                        ->always()
                        ->then($investigatorNorm)
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    protected function addFallbackThemeResolverNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('fallback');

        $rootNode
            ->info('fallback theme resolver configuration')
            ->canBeEnabled();

        $this->configureThemeResolverNode($rootNode, true);

        return $rootNode;
    }

    protected function addPrimaryThemeResolverNode()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('primary');

        $rootNode
            ->info('theme resolver configuration')
            ->isRequired();

        $this->configureThemeResolverNode($rootNode);

        return $rootNode;
    }

    protected function configureThemeResolverNode(ArrayNodeDefinition $node, $enabledCheck = false)
    {
        $node
            ->fixXmlConfig('argument')
            ->children()
                ->scalarNode('id')->cannotBeEmpty()->end()
                ->enumNode('type')
                    ->values(array('in_memory', 'cookie', 'service', 'session'))
                    ->info('a type of theme resolver')
                ->end()
                ->arrayNode('arguments')
                    ->info('arguments to be passed to the theme resolver')
                    ->cannotBeEmpty()
                    ->prototype('variable')->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return array($v);
                        })
                    ->end()
                ->end()
            ->end()
            ->beforeNormalization()
                ->ifString()
                ->then(function ($v) {
                    return array('id' => $v);
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['id']) && !isset($v['type']);
                })
                ->then(function ($v) {
                    $v['type'] = 'service';

                    return $v;
                })
            ->end()
            ->validate()
                ->ifTrue(function ($v) use ($enabledCheck) {
                    return (!$enabledCheck || isset($v['enabled'])) && !isset($v['id']) && !isset($v['type']);
                })
                ->thenInvalid('At least you must specify "id" or "type" attribute.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) use ($enabledCheck) {
                    return (!$enabledCheck || isset($v['enabled'])) && isset($v['id']) && isset($v['type']) && $v['type'] != 'service';
                })
                ->thenInvalid('For the "id" attribute the only acceptable value for the "type" attribute is "service" and this value is not required.')
            ->end();
    }
}
