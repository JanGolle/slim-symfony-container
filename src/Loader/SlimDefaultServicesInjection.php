<?php
declare(strict_types=1);

namespace JanGolle\SlimSymfonyContainer\Loader;

use Slim\Collection;
use Slim\DefaultServicesProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SlimDefaultServicesInjection
 */
class SlimDefaultServicesInjection implements ServiceInjectionInterface
{
    private const DEFAULT_PROPERTIES_NAMESPACE = 'slim.settings';

    /**
     * @var string
     */
    private $slimSettingsConfigNamespace;

    /**
     * @var DefaultServicesProvider
     */
    private $slimServicesProvider;

    /**
     * Default settings
     *
     * @var array
     */
    private $slimDefaultSettings = [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ];

    /**
     * @param string $slimSettingsConfigNamespace
     * @param DefaultServicesProvider|null $slimServicesProvider
     */
    public function __construct(
        string $slimSettingsConfigNamespace = self::DEFAULT_PROPERTIES_NAMESPACE,
        DefaultServicesProvider $slimServicesProvider = null
    ) {
        $this->slimSettingsConfigNamespace = trim($slimSettingsConfigNamespace, '.');
        $this->slimServicesProvider = is_null($slimServicesProvider)
            ? new DefaultServicesProvider()
            : $slimServicesProvider;
    }

    public function injectServices(ContainerBuilder $container)
    {
        $definedSettings = [];
        $settingsParamsMap = [];

        if ($container->has('settings') && $container->get('settings') instanceof Collection) {
            $definedSettings = $container->get('settings')->all();
        }

        foreach (array_merge($this->slimDefaultSettings, $definedSettings) as $key => $value) {
            $settingName = sprintf('%s.%s', $this->slimSettingsConfigNamespace, $key);
            $container->getParameterBag()->has($settingName)
                ?: $container->getParameterBag()->set($settingName, $value);
            $settingsParamsMap[$key] = "%{$settingName}%";
        }

        $container->register('settings', Collection::class)->addArgument($settingsParamsMap);
        $containerExtractor = new \ArrayObject(array_flip($container->getServiceIds()));

        $this->slimServicesProvider->register($containerExtractor);

        foreach ($containerExtractor as $name => $serviceClosure) {
            $container->has($name) ?: $container->set($name, call_user_func($serviceClosure, $container));
        }
    }
}

