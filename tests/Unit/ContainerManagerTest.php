<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer\Test\Unit;

use JanGolle\SlimSymfonyContainer\ContainerManager;
use JanGolle\SlimSymfonyContainer\Loader\ServiceInjectionInterface;
use JanGolle\SlimSymfonyContainer\Loader\ServiceInjectionLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class ContainerManagerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testResolveContainerPositiveWithClassName()
    {
        $injectionLoader = $this->createMock(LoaderInterface::class);
        $injectionLoader->expects($this->never())
            ->method('load');

        $containerClass = TestContainer::class;

        $containerManager = $this->createPartialMock(ContainerManager::class, ['provideLoader']);

        $containerManager->expects($this->once())
            ->method('provideLoader')
            ->with($this->isInstanceOf($containerClass))
            ->willReturn($injectionLoader);

        $this->assertInstanceOf(ContainerBuilder::class, $containerManager->resolveContainer($containerClass));
    }

    /**
     * @throws \Exception
     */
    public function testResolveContainerPositiveWithInstance()
    {
        $injectionLoader = $this->createMock(LoaderInterface::class);
        $injectionLoader->expects($this->never())
            ->method('load');

        $container = new TestContainer();

        $containerManager = $this->createPartialMock(ContainerManager::class, ['provideLoader']);

        $containerManager->expects($this->once())
            ->method('provideLoader')
            ->with($container)
            ->willReturn($injectionLoader);

        $this->assertSame($container, $containerManager->resolveContainer($container));
    }

    /**
     * @throws \Exception
     */
    public function testResolveContainerPositiveWithInjections()
    {
        $injections = [
            $this->createMock(ServiceInjectionInterface::class),
            $this->createMock(ServiceInjectionInterface::class),
        ];

        $injectionLoader = $this->createMock(LoaderInterface::class);
        $injectionLoader->expects($this->exactly(count($injections)))
            ->method('load')
            ->withConsecutive(...$injections);

        $container = new TestContainer();

        $containerManager = $this->createPartialMock(ContainerManager::class, ['provideLoader']);

        $containerManager->expects($this->once())
            ->method('provideLoader')
            ->with($container)
            ->willReturn($injectionLoader);

        $this->assertSame($container, $containerManager->resolveContainer($container, ...$injections));
    }

    /**
     * @throws \Exception
     *
     * @expectedException RuntimeException
     */
    public function testResolveContainerNegativeUnsupportedClass()
    {
        $injectionLoader = $this->createMock(LoaderInterface::class);
        $injectionLoader->expects($this->never())
            ->method('load');

        $container = 'unsupportedClass';

        $containerManager = $this->createPartialMock(ContainerManager::class, ['provideLoader']);

        $containerManager->expects($this->never())
            ->method('provideLoader');

        $containerManager->resolveContainer($container);
    }

    /**
     * @throws \Exception
     */
    public function testProvideLoaderPositive()
    {
        $container = new TestContainer();
        $containerManager = new ContainerManager();

        $provideLoaderMethod = (new \ReflectionClass($containerManager))->getMethod('provideLoader');
        $provideLoaderMethod->setAccessible(true);

        $loader = $provideLoaderMethod->invokeArgs($containerManager, [$container]);

        $this->assertInstanceOf(ServiceInjectionLoader::class, $loader);
    }
}

class TestContainer extends ContainerBuilder
{
}
