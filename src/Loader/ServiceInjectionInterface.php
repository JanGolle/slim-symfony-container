<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer\Loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ServiceInjectionInterface
{
    /**
     * Load services into container
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function injectServices(ContainerBuilder $container);
}