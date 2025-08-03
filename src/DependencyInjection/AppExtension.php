<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * An app extension.
 *
 * This class integrates settings with symfony's DI container.
 * @package App\Configuration
 */
class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $section => $options) {
            foreach ($options as $name => $value) {
                $container->setParameter("app.{$section}.{$name}", $value);
            }
        }
    }
}