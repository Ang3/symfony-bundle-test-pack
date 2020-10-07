Symfony bundle test pack
========================

[![Build Status](https://travis-ci.org/Ang3/symfony-bundle-test-pack.svg?branch=master)](https://travis-ci.org/Ang3/symfony-bundle-test-pack) 
[![Latest Stable Version](https://poser.pugx.org/ang3/symfony-bundle-test-pack/v/stable)](https://packagist.org/packages/ang3/symfony-bundle-test-pack) 
[![Latest Unstable Version](https://poser.pugx.org/ang3/symfony-bundle-test-pack/v/unstable)](https://packagist.org/packages/ang3/symfony-bundle-test-pack) 
[![Total Downloads](https://poser.pugx.org/ang3/symfony-bundle-test-pack/downloads)](https://packagist.org/packages/ang3/symfony-bundle-test-pack)

This pack provides tools to write functional tests for reusable bundles.

No need to create a test application, or 
tons of micro kernel files: this pack provides a test case classes with a contextual kernel 
that you can configure easily before using it.

**The idea:** create base classes extending ```Symfony\Bundle\FrameworkBundle\Test\KernelTestCase``` 
and override the method ```KernelTestCase::createKernel()``` to create a kernel from a context instead of basic options.

Summary
=======

- [Installation](#installation)
- [Usage](#usage)
    - [Write your test](#write-your-test)
        - [Disable auto-boot](#disable-auto-boot)
    - [Working with kernel context](#working-with-kernel-context)
        - [Register bundles](#register-bundles)
        - [Register extensions](#register-extensions)
        - [Define parameters](#define-parameters)
        - [Private services and aliases](#private-services-and-aliases)
        - [Configure the container](#configure-the-container)
        - [Build the container](#build-the-container)
        - [Configure routing](#configure-routing)

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your app directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require ang3/symfony-bundle-test-pack --dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Usage
=====

Write your test
---------------

### Test case kernel

Create a new PHP file in your tests directory (i.e ```tests/ServicesTest.php```)  and paste the content below:

```php
namespace Tests;

use Ang3\Bundle\Test\BundleTestCase;
use Ang3\Bundle\Test\Kernel\KernelContext;

class ServicesTest extends BundleTestCase
{
    protected static function configureKernel(KernelContext $context): void
    {
        // TODO: configure the kernel of the test class with the given context instance.
    }
}
```

On each test, the kernel is destroyed, and a new one is created from a new context class. 
This context is sent to the method ```configureKernel()``` to allow you to configure the kernel.

If you want to write a **web** test case, you just have to change the class ```Ang3\Bundle\Test\BundleTestCase``` 
by the class ```Ang3\Bundle\Test\WebTestCase```.

### Disable auto-boot

By default, the kernel is automatically booted on each test.
However, you can disable this feature by overriding the static parameter ```$autoBoot```:

```php
// ...

class ServicesTest extends BundleTestCase
{
    protected static $autoBoot = false;

    // ...
}
```

### Standalone

To create an isolated kernel in a test, create it directly from a context:

```php
use Ang3\Bundle\Test\Kernel\ContextualKernel;

$kernel = ContextualKernel::createContext()
    // Configure the context...
    // Then create the kernel
    ->createKernel();
```

Working with kernel context
---------------------------

By default, the kernel is just... a micro-kernel. No bundles registered. 
All the logic resides on the context to configure the kernel as to your needs.

### Register bundles

The first thing you probably want to do is to configure the kernel to boot with your reusable bundle. 
To do that, you just have to add a new instance of your bundle like below:

```php
/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context->addBundle(new MyBundle());
```

You can chain all methods to add more bundles:

```php
/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context
    ->addBundle(new FrameworkBundle())
    ->addBundle(new DoctrineBunble())
    ->addBundle(new MyBundle());
```

### Register extensions

In case of adding bundle ```FrameworkBundle``` for example, you will get an error due to missing services or parameters. 
Indeed, when you install a bundle, you often have to add some configurations files - This is generally automatically 
done with Flex. But... not here. No configuration is loaded and naturally the loading of added bundle will fail.

That's why the context allows you to register extension easily:

```php
/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context
    ->getExtensions()
    ->add('my_extension', $config = []);
```

To make the life easier, this pack provides default configs for the bundles ```framework-bundle```, 
```security-bundle```, ```doctrine-bundle``` and ```api-core``` (API platform bundle):

```php
/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context
    ->getExtensions()
    ->addFrameworkExtension()
    ->addSecurityExtension() // Registers automatically the configuration of framework-bundle if missing
    ->addDoctrineExtension() // Registers automatically the configuration of framework-bundle if missing
    ->addApiPlatformExtension() // Registers automatically the configuration of framework-bundle and doctrine-bundle if missing
    ->addSwiftmailerExtension(); // Registers automatically the configuration of swiftmailer-bundle
```

If one of these bundles is added but no extension is configured for, 
then the kernel will automatically add the default configuration for this bundle.

### Define parameters

You can set parameters during the container configuration like below:

```php
/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context->setParameter('app.my_parameter', 'value');
```

#### Doctrine & API Platform

If you use the default Doctrine or API Platform bundle configuration, you need to set the parameter 
```kernel.doctrine_entity_dir```.

### Private services and aliases

> Keep in mind that, because of how Symfony's service container work, unused services are removed from the container. 
> This means that if you have a private service not used by any other service, 
> Symfony will remove it and you won't be able to get it as explained in this article. 
> The solution is to define the service as public explicitly so Symfony doesn't remove it.
> - [Symfony blog](https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing)

In case of reusable bundles, sometimes you will create a service for others bundles or applications using your 
bundle. However, these unsued privates services and aliases in your bundle will be removed from the container 
during tests and you could get an error like below:

> Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException: 
> The "App\SomeService" service or alias has been removed or inlined when the container was compiled. 
> You should either make it public, or stop using the container directly and use dependency injection instead.

This pack provides a trick to allow you to make these services and aliases public in test case only. 
To do that, you just have to define these services and aliases in the context:

```php
/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context
    ->setPrivateService('App\MyPrivateService')
    ->setPrivateAlias('App\MyPrivateService $myAlias');
```

Internally, a compiler pass will make these services and aliases public to be tested.

### Configure the container

You can control the container configuration of the micro-kernel with a callable. 
The container configurator is passed to this callable when the kernel configures it.

```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context->setContainer(function(ContainerConfigurator $containerConfigurator) {
    // Add extension manually
    $containerConfigurator->extension($name = 'my_extension', $config = []);

    // Add service manually
    $containerConfigurator
        ->services()
        ->set('app.my_service', 'App\FooService')
        ->set('App\BarService');

    // Add parameter manually
    $containerConfigurator
        ->parameters()
        ->set('app.my_parameter', 'parameter_value');
});
```

### Build the container

The context allows you to add custom logic on container building process in case of specific needs. 
It resides on a callable: the container builder is passed to this callable when the kernel builds it:

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context->setBuilder(function(ContainerBuilder $containerBuilder) {
    // ...
});
```

### Configure routing

To configure the routing, register a callable to handle the routing configurator:

```php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/** @var \Ang3\Bundle\Test\Kernel\KernelContext $context */
$context->setRouting(function(RoutingConfigurator $routingConfigurator) {
    // ...
});
```