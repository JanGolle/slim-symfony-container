[![Build Status](https://travis-ci.org/JanGolle/slim-symfony-container.svg?branch=master)](https://travis-ci.org/JanGolle/slim-symfony-container)
[![codecov](https://codecov.io/gh/JanGolle/slim-symfony-container/branch/master/graph/badge.svg)](https://codecov.io/gh/JanGolle/slim-symfony-container)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/jangolle/slim-symfony-container.svg)](https://packagist.org/packages/jangolle/slim-symfony-container)
[![Packagist](https://img.shields.io/packagist/v/jangolle/slim-symfony-container.svg)](https://packagist.org/packages/jangolle/slim-symfony-container)

# Slim with Symfony DI Container integration

Easily resolve Symfony `ContainerBuilder` and setup in it all default Slim Application dependencies if necessary. Loader resolve symfony configuration params and setup slim default settings with params to symfony `ParameterBag`.

# Installation

Library is available on [Packagist](https://packagist.org/packages/jangolle/slim-symfony-container).

Installation via composer is the recommended way to install it.

Just add this line to `required` section of your `composer.json` file:

```json
"jangolle/slim-symfony-container": "~1.0"
```

or just run in console

```sh
cd /path/to/your/project
composer require jangolle/slim-symfony-container
```
# Default usage

You can directly create `ContainerBuilder` and use it from scratch to setup with `SlimDefaultServicesInjection` like this:

```php
<?php
declare(strict_types=1);

use JanGolle\SlimSymfonyContainer\ContainerManager;
use JanGolle\SlimSymfonyContainer\Loader\SlimDefaultServicesInjection;
use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerManager = new ContainerManager();
$container = new ContainerBuilder();

$container = $containerManager->resolveContainer($container, new SlimDefaultServicesInjection());

$app = new \Slim\App($container);

//setup routes or something

$app->run();

```

# Use with Symfony configs

If you want your symfony configuration files in your project you can actually do something like:

```php
<?php
declare(strict_types=1);

use JanGolle\SlimSymfonyContainer\ContainerManager;
use JanGolle\SlimSymfonyContainer\Loader\SlimDefaultServicesInjection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$containerManager = new ContainerManager();
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/path/to/config'));
$loader->load('services.yaml');

$container = $containerManager->resolveContainer($container, new SlimDefaultServicesInjection());

$app = new \Slim\App($container);

//setup routes or something

$app->run();
```

Your `services.yaml` parameters block might look like this:

```yaml
parameters:
  slim.settings.httpVersion: '1.1'
  slim.settings.responseChunkSize: 4096
  slim.settings.displayErrorDetails: !php/const DEBUG

```

**NOTE:** You can override default SLIM settings in your config file with your custom values.. or not, it's up to you :) All SLIM necessary settings will be applied to container injection with your params or not.

# Custom wrapper class

If you have your own container that is instance of `Symfony\Component\DependencyInjection\ContainerBuilder` and you want to setup it with Slim dependencies you can do like this:

```php
$container = new YourCustomContainer($fullOfParams);//instance of Symfony\Component\DependencyInjection\ContainerBuilder
$container = $containerManager->resolveContainer($container, new SlimDefaultServicesInjection());

```

or you can just instantiate it with default constructor via `::class` as first arg of `resolveContainer`:

```php
$container = $containerManager->resolveContainer(YourCustomContainer::class, new SlimDefaultServicesInjection());

```

# Container access inside app

Inside your routes scope `$this` will return our container which is actually instance of `Symfony\Component\DependencyInjection\ContainerBuilder`

```php
//...
$app->get(
    '/your/route',
    function (Request $request, Response $response, array $args) {
        $this->get('someService')->doSomeStuff();

        return $response;
    }
);
```

# PhpStorm Symfony Plugin support

If you are using **PhpStorm** IDE you can install in it `Symfony plugin` and get access to typehinting and IDE autocomplite for services.


Thanks for your attention!
