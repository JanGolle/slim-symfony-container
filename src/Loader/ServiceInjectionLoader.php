<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ServiceInjectionLoader
 */
class ServiceInjectionLoader extends Loader
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServiceInjectionInterface $resource
     * @param null                      $type
     */
    public function load($resource, $type = null)
    {
        $resource->injectServices($this->container);
    }

    /**
     * @param ServiceInjectionInterface $resource
     * @param null                      $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return $resource instanceof ServiceInjectionInterface;
    }
}

