<?php

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('app');
        $rootNode = $treeBuilder->getRootNode();

        // Node definition
        $rootNode
            ->children()
                ->arrayNode('auth')
                    ->children()
                        ->booleanNode('enable_throttling')
                            ->defaultFalse()->info('Enable or disable authentication throttling (bans user after N failed login attempts)')
                        ->end()
                        ->integerNode('max_login_failures')
                            ->defaultFalse()->info('Max failed login attempts before ban.')
                        ->end()
                        ->integerNode('cooldown_seconds')
                            ->defaultFalse()->info('Cooldown period (user cannot to loging during this time).')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('general')
                    ->children()
                        ->scalarNode('title')
                            ->defaultValue('Exedra')
                            ->info('Beauty & Lifestyle Jobs')
                        ->end()
                        ->scalarNode('title_separator')
                            ->defaultValue('/')
                            ->info('A symbol to separate section in page title.')
                        ->end()
                        ->scalarNode('title_position')
                            ->defaultValue('right')
                            ->info('A position of app title in page title.')
                        ->end()
                        ->scalarNode('mail_from_address')
                            ->defaultValue('info@exedralifestyle.com')
                            ->info('Default mail sender email.')
                        ->end()
                        ->scalarNode('mail_from_name')
                            ->defaultValue('Exedra Lifestyle')
                            ->info('Default mail sender name.')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('registration')
                    ->children()
                        ->booleanNode('use_recaptcha')
                            ->defaultFalse()
                            ->info('enable reCAPTCHA for registration')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('recaptcha')
                    ->children()
                        ->scalarNode('site_key')
                            ->defaultNull()
                            ->info('reCAPTCHA site key')
                        ->end()
                        ->scalarNode('secret_key')
                            ->defaultNull()
                            ->info('reCAPTCHA secret key')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}