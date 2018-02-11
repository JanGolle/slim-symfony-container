<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer;

use JanGolle\SlimSymfonyContainer\Loader\ServiceInjectionInterface;
use JanGolle\SlimSymfonyContainer\Loader\ServiceInjectionLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Class ContainerManager
 */
class ContainerManager
{
    /**
     * @param string|ContainerBuilder $container
     * @param ServiceInjectionInterface[]  ...$injections
     *
     * @return ContainerBuilder
     * @throws \Exception
     */
    public function resolveContainer(
        $container,
        ServiceInjectionInterface... $injections
    ) : ContainerBuilder {
        if (is_string($container) && is_subclass_of($container, ContainerBuilder::class)) {
            $container = new $container();
        } elseif (!$container instanceof ContainerBuilder) {
            throw new RuntimeException(
                sprintf('"$container" must be instance of %s or className subclass to it', ContainerBuilder::class)
            );
        }

        $injectionLoader = $this->provideLoader($container);

        foreach ($injections as $injection) {
            $injectionLoader->load($injection);
        }

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return LoaderInterface
     */
    protected function provideLoader(ContainerBuilder $container) : LoaderInterface
    {
        return new ServiceInjectionLoader($container);
    }
}

