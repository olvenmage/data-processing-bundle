<?php

namespace Olveneer\DataProcessorBundle\DependencyInjection;

use Olveneer\TwigComponentsBundle\Component\TwigComponentInterface;
use Olveneer\TwigComponentsBundle\Component\TwigComponentMixin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class OlveneerDataProcessorExtension
 * @package Olveneer\TwigComponentsBundle\DependencyInjection
 */
class OlveneerDataProcessorExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
    }
}
