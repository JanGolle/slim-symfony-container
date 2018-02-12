<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer\Test\Unit\Loader;

use JanGolle\SlimSymfonyContainer\Loader\SlimDefaultServicesInjection;
use PHPUnit\Framework\TestCase;
use Slim\Collection;
use Slim\DefaultServicesProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SlimDefaultServicesInjectionTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testConstructorPositiveDefault()
    {
        $injection = new SlimDefaultServicesInjection();

        $reflection = new \ReflectionClass($injection);
        $namespaceProperty = $reflection->getProperty('slimSettingsConfigNamespace');
        $namespaceProperty->setAccessible(true);

        $servicesProviderProperty = $reflection->getProperty('slimServicesProvider');
        $servicesProviderProperty->setAccessible(true);

        $defaultValue = $reflection->getConstant('DEFAULT_PROPERTIES_NAMESPACE');

        $this->assertSame($defaultValue, $namespaceProperty->getValue($injection));
        $this->assertInstanceOf(DefaultServicesProvider::class, $servicesProviderProperty->getValue($injection));
    }

    /**
     * @throws \Exception
     */
    public function testConstructorPositiveStringWithTrailingDot()
    {
        $givenNamespace = 'some.namespace.';
        $expectedNamespace = 'some.namespace';
        $injection = new SlimDefaultServicesInjection($givenNamespace);

        $reflection = new \ReflectionClass($injection);
        $namespaceProperty = $reflection->getProperty('slimSettingsConfigNamespace');
        $namespaceProperty->setAccessible(true);

        $servicesProviderProperty = $reflection->getProperty('slimServicesProvider');
        $servicesProviderProperty->setAccessible(true);

        $this->assertSame($expectedNamespace, $namespaceProperty->getValue($injection));
        $this->assertInstanceOf(DefaultServicesProvider::class, $servicesProviderProperty->getValue($injection));
    }

    /**
     * @throws \Exception
     */
    public function testConstructorPositiveStringWithoutTrailingDot()
    {
        $expectedNamespace = 'some.namespace';
        $injection = new SlimDefaultServicesInjection($expectedNamespace);

        $reflection = new \ReflectionClass($injection);
        $namespaceProperty = $reflection->getProperty('slimSettingsConfigNamespace');
        $namespaceProperty->setAccessible(true);

        $servicesProviderProperty = $reflection->getProperty('slimServicesProvider');
        $servicesProviderProperty->setAccessible(true);

        $this->assertSame($expectedNamespace, $namespaceProperty->getValue($injection));
        $this->assertInstanceOf(DefaultServicesProvider::class, $servicesProviderProperty->getValue($injection));
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testConstructorPositiveWithNotDefaultServiceProvider()
    {
        $expectedNamespace = 'some.namespace';
        $customServiceProvider = $this->createMock(DefaultServicesProvider::class);
        $injection = new SlimDefaultServicesInjection($expectedNamespace, $customServiceProvider);

        $reflection = new \ReflectionClass($injection);
        $namespaceProperty = $reflection->getProperty('slimSettingsConfigNamespace');
        $namespaceProperty->setAccessible(true);

        $servicesProviderProperty = $reflection->getProperty('slimServicesProvider');
        $servicesProviderProperty->setAccessible(true);

        $this->assertSame($expectedNamespace, $namespaceProperty->getValue($injection));
        $this->assertSame($customServiceProvider, $servicesProviderProperty->getValue($injection));
    }

    /**
     * @throws \ReflectionException
     */
    public function testInjectServicesPositiveWithoutSettingsWithEmptyParameterBag()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $serviceProvider = $this->createMock(DefaultServicesProvider::class);
        $settingsDefinition = $this->createMock(Definition::class);
        $namespace = 'test.namespace';
        $slimSettingsKey = 'settings';
        $slimSettingsClass = Collection::class;

        $serviceProvider->expects($this->once())
            ->method('register')
            ->with($this->isInstanceOf(\ArrayAccess::class));

        $injection = new SlimDefaultServicesInjection($namespace, $serviceProvider);

        $defaultSettingsProperty = (new \ReflectionProperty($injection, 'slimDefaultSettings'));
        $defaultSettingsProperty->setAccessible(true);

        $settings = $defaultSettingsProperty->getValue($injection);
        $settingsCount = count($settings);

        $container->expects($this->once())
            ->method('has')
            ->with($slimSettingsKey)
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get');

        $parameterBag->expects($this->exactly($settingsCount))
            ->method('has')
            ->willReturn(false);

        $settingsWithParams = $settingsParamsMap = [];
        foreach ($settings as $key => $value) {
            $settingName = sprintf('%s.%s', $namespace, $key);
            $settingsWithParams[] = [$settingName, $value];
            $settingsParamsMap[$key] = "%{$settingName}%";
        }

        $parameterBag->expects($this->exactly($settingsCount))
            ->method('set')
            ->withConsecutive(...$settingsWithParams);

        $container->expects($this->atLeast($settingsCount))
            ->method('getParameterBag')
            ->willReturn($parameterBag);

        $settingsDefinition->expects($this->once())
            ->method('addArgument')
            ->with($settingsParamsMap)
            ->willReturnSelf();

        $container->expects($this->once())
            ->method('register')
            ->with($slimSettingsKey, $slimSettingsClass)
            ->willReturn($settingsDefinition);

        $container->expects($this->once())
            ->method('getServiceIds')
            ->willReturn([]);

        $injection->injectServices($container);
    }

    /**
     * @throws \ReflectionException
     */
    public function testInjectServicesPositiveWithSettingsWithEmptyParameterBag()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $serviceProvider = $this->createMock(DefaultServicesProvider::class);
        $settingsDefinition = $this->createMock(Definition::class);
        $namespace = 'test.namespace';
        $slimSettingsKey = 'settings';
        $slimSettingsClass = Collection::class;

        $serviceProvider->expects($this->once())
            ->method('register')
            ->with($this->isInstanceOf(\ArrayAccess::class));

        $injection = new SlimDefaultServicesInjection($namespace, $serviceProvider);

        $defaultSettingsProperty = (new \ReflectionProperty($injection, 'slimDefaultSettings'));
        $defaultSettingsProperty->setAccessible(true);

        $settings = $defaultSettingsProperty->getValue($injection);

        $definedSettings = array_merge(
            !empty($settings) ? array_slice($settings, 0, count($settings) - 1, true) : [],
            [
                'customSettingKey-1' => 0.42,
                'customSettingKey-2' => 10,
                'customSettingKey-3' => 'value',
                'customSettingKey-4' => new \stdClass(),
            ]
        );
        $definedSettingsCollection = new $slimSettingsClass($definedSettings);

        $settings = array_merge($settings, $definedSettings);
        $settingsCount = count($settings);

        $container->expects($this->once())
            ->method('has')
            ->with($slimSettingsKey)
            ->willReturn(true);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with($slimSettingsKey)
            ->willReturn($definedSettingsCollection);

        $parameterBag->expects($this->exactly($settingsCount))
            ->method('has')
            ->willReturn(false);

        $settingsWithParams = $settingsParamsMap = [];
        foreach ($settings as $key => $value) {
            $settingName = sprintf('%s.%s', $namespace, $key);
            $settingsWithParams[] = [$settingName, $value];
            $settingsParamsMap[$key] = "%{$settingName}%";
        }

        $parameterBag->expects($this->exactly($settingsCount))
            ->method('set')
            ->withConsecutive(...$settingsWithParams);

        $container->expects($this->atLeast($settingsCount))
            ->method('getParameterBag')
            ->willReturn($parameterBag);

        $settingsDefinition->expects($this->once())
            ->method('addArgument')
            ->with($settingsParamsMap)
            ->willReturnSelf();

        $container->expects($this->once())
            ->method('register')
            ->with($slimSettingsKey, $slimSettingsClass)
            ->willReturn($settingsDefinition);

        $container->expects($this->once())
            ->method('getServiceIds')
            ->willReturn([]);

        $injection->injectServices($container);
    }

    /**
     * @throws \ReflectionException
     */
    public function testInjectServicesPositiveWithoutSettingsWithNotEmptyParameterBag()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $serviceProvider = $this->createMock(DefaultServicesProvider::class);
        $settingsDefinition = $this->createMock(Definition::class);
        $namespace = 'test.namespace';
        $slimSettingsKey = 'settings';
        $slimSettingsClass = Collection::class;

        $serviceProvider->expects($this->once())
            ->method('register')
            ->with($this->isInstanceOf(\ArrayAccess::class));

        $injection = new SlimDefaultServicesInjection($namespace, $serviceProvider);

        $defaultSettingsProperty = (new \ReflectionProperty($injection, 'slimDefaultSettings'));
        $defaultSettingsProperty->setAccessible(true);

        $settings = $defaultSettingsProperty->getValue($injection);
        $settingsCount = count($settings);

        $container->expects($this->once())
            ->method('has')
            ->with($slimSettingsKey)
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get');

        $defaultSettingsDataPart = !empty($settings) ? array_slice($settings, 0, count($settings) - 1, true) : [];
        $givenParameterBagData = array_merge(
            array_fill_keys(array_keys($defaultSettingsDataPart), '%someCustomParam%'),
            [
                'someCustomSettingKey1' => '%someCustomParam%',
                'someCustomSettingKey2' => '%someCustomParam%',
                'someCustomSettingKey3' => '%someCustomParam%',
            ]
        );
        $parameterBag = new TestParameterBag($givenParameterBagData);

        $settingsParamsMap = [];
        foreach ($settings as $key => $value) {
            $settingName = sprintf('%s.%s', $namespace, $key);
            $settingsParamsMap[$key] = $givenParameterBagData[$key] = "%{$settingName}%";
        }

        $container->expects($this->atLeast($settingsCount))
            ->method('getParameterBag')
            ->willReturn($parameterBag);

        $settingsDefinition->expects($this->once())
            ->method('addArgument')
            ->with($settingsParamsMap)
            ->willReturnSelf();

        $container->expects($this->once())
            ->method('register')
            ->with($slimSettingsKey, $slimSettingsClass)
            ->willReturn($settingsDefinition);

        $container->expects($this->once())
            ->method('getServiceIds')
            ->willReturn([]);

        $injection->injectServices($container);
    }

    /**
     * @throws \ReflectionException
     */
    public function testInjectServicesPositiveWithSettingsWithNotEmptyParameterBag()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $serviceProvider = $this->createMock(DefaultServicesProvider::class);
        $settingsDefinition = $this->createMock(Definition::class);
        $namespace = 'test.namespace';
        $slimSettingsKey = 'settings';
        $slimSettingsClass = Collection::class;

        $serviceProvider->expects($this->once())
            ->method('register')
            ->with($this->isInstanceOf(\ArrayAccess::class));

        $injection = new SlimDefaultServicesInjection($namespace, $serviceProvider);

        $defaultSettingsProperty = (new \ReflectionProperty($injection, 'slimDefaultSettings'));
        $defaultSettingsProperty->setAccessible(true);

        $settings = $defaultSettingsProperty->getValue($injection);

        $definedSettings = array_merge(
            !empty($settings) ? array_slice($settings, 0, count($settings) - 1, true) : [],
            [
                'customSettingKey-1' => 0.42,
                'customSettingKey-2' => 10,
                'customSettingKey-3' => 'value',
                'customSettingKey-4' => new \stdClass(),
            ]
        );
        $definedSettingsCollection = new $slimSettingsClass($definedSettings);

        $settings = array_merge($settings, $definedSettings);
        $settingsCount = count($settings);

        $container->expects($this->once())
            ->method('has')
            ->with($slimSettingsKey)
            ->willReturn(true);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with($slimSettingsKey)
            ->willReturn($definedSettingsCollection);

        $defaultSettingsDataPart = !empty($settings) ? array_slice($settings, 0, count($settings) - 1, true) : [];
        $givenParameterBagData = array_merge(
            array_fill_keys(array_keys($defaultSettingsDataPart), '%someCustomParam%'),
            [
                'someCustomSettingKey1' => '%someCustomParam%',
                'someCustomSettingKey2' => '%someCustomParam%',
                'someCustomSettingKey3' => '%someCustomParam%',
            ]
        );
        $parameterBag = new TestParameterBag($givenParameterBagData);

        $settingsParamsMap = [];
        foreach ($settings as $key => $value) {
            $settingName = sprintf('%s.%s', $namespace, $key);
            $settingsParamsMap[$key] = $givenParameterBagData[$key] = "%{$settingName}%";
        }

        $container->expects($this->atLeast($settingsCount))
            ->method('getParameterBag')
            ->willReturn($parameterBag);

        $settingsDefinition->expects($this->once())
            ->method('addArgument')
            ->with($settingsParamsMap)
            ->willReturnSelf();

        $container->expects($this->once())
            ->method('register')
            ->with($slimSettingsKey, $slimSettingsClass)
            ->willReturn($settingsDefinition);

        $container->expects($this->once())
            ->method('getServiceIds')
            ->willReturn([]);

        $injection->injectServices($container);
    }
}

class TestParameterBag implements ParameterBagInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function clear()
    {
    }

    public function add(array $parameters)
    {
    }

    public function all()
    {
        return $this->data;
    }

    public function get($name)
    {
    }

    public function remove($name)
    {
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function has($name)
    {
        return isset($this->data[$name]);
    }

    public function resolve()
    {
    }

    public function resolveValue($value)
    {
    }

    public function escapeValue($value)
    {
    }

    public function unescapeValue($value)
    {
    }

}
