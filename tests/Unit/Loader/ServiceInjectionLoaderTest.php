<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer\Test\Unit\Loader;

use JanGolle\SlimSymfonyContainer\Loader\ServiceInjectionInterface;
use JanGolle\SlimSymfonyContainer\Loader\ServiceInjectionLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceInjectionLoaderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testLoadPositiveWithoutType()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $resource = $this->createMock(ServiceInjectionInterface::class);

        $resource->expects($this->once())
            ->method('injectServices')
            ->with($container);

        $loader = new ServiceInjectionLoader($container);
        $loader->load($resource);
    }

    /**
     * @throws \ReflectionException
     */
    public function testLoadPositiveWithAnyType()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $resource = $this->createMock(ServiceInjectionInterface::class);
        $type = $this->anything();

        $resource->expects($this->once())
            ->method('injectServices')
            ->with($container);

        $loader = new ServiceInjectionLoader($container);
        $loader->load($resource, $type);
    }

    /**
     * @throws \ReflectionException
     *
     * @expectedException \Error
     * @expectedExceptionMessage Call to a member function injectServices() on string
     */
    public function testLoadNegativeWithUnsupportedResource()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $resource = 'unsupported';
        $type = $this->anything();

        $loader = new ServiceInjectionLoader($container);
        $loader->load($resource, $type);
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testSupportsPositive()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $resource = $this->createMock(ServiceInjectionInterface::class);
        $type = $this->anything();

        $loader = new ServiceInjectionLoader($container);
        $this->assertTrue($loader->supports($resource, $type));
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testSupportsNegative()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $resource = 'unsupported';
        $type = $this->anything();

        $loader = new ServiceInjectionLoader($container);
        $this->assertFalse($loader->supports($resource, $type));
    }
}
