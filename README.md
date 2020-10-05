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
    - [Configure the kernel](#configure-the-kernel)
    - [Assertions](#assertions)
        - [Container](#container)

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

Create a new PHP file in your tests directory (i.e ```tests/ServicesTest.php```)  and paste the content below:

```php
namespace Tests;

use Ang3\Bundle\Test\BundleTestCase;
use Ang3\Bundle\Test\KernelContext;

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

Configure the kernel
--------------------

### Disable auto-boot

By default, the kernel is automatically booted. 
However, you can disable this feature by overriding the static parameter ```$autoBoot```:

```php
// ...

class ServicesTest extends BundleTestCase
{
    protected static $autoBoot = false;

    // ...
}
```

### Register bundles

The first thing you probably want to do is to configure the kernel to boot with your reusable bundle. 
To do that, you just have to add a new instance of your bundle like below:

```php
/** @var \Ang3\Bundle\Test\KernelContext $context */
$context->addBundle(new MyBundle());
```

You can chain all methods to add more bundles:

```php
/** @var \Ang3\Bundle\Test\KernelContext $context */
$context
    ->addBundle(new DoctrineBunble())
    ->addBundle(new MyBundle());
```

**Good to know:** 
The bundle ```Symfony\Bundle\FrameworkBundle\FrameworkBundle``` is automatically registered, **no need to add it**.

### Configure bundles

Then, you probably want to configure bundle extensions to test the bundle configuration for example. 
The context allows you to add extension configuration:

```php
/** @var \Ang3\Bundle\Test\KernelContext $context */
$context
    ->setExtension('doctrine', [])
    ->setExtension('my_bundle', []);
```

### Parameters and services

You can use the context to add default parameters and services:

```php
/** @var \Ang3\Bundle\Test\KernelContext $context */
$context
    ->setParameter('app.my_param_1', 'foo')
    ->setParameter('app.my_param_2', 'bar');
```

```php
/** @var \Ang3\Bundle\Test\KernelContext $context */
$context
    ->setService('app.my_provider', 'Foo\Bar\MyProvider')
    ->setService('Foo\Bar\MyProvider');
```

### Routes

To test your controllers, you probably want to import your routes files. 
To do that, add files realpath to the context:

```php
/** @var \Ang3\Bundle\Test\KernelContext $context */
$context
    ->addRoute(__DIR__.'../config/routes/routes1.yaml')
    ->addRoute(__DIR__.'../config/routes/routes2.xml');
```

Assertions
----------

### Container

The bundle test class provides some useful methods to assert container parameters and services.

#### Parameters

```php
$parameterValue = $this->assertParameter('app.my_param', 'foo');
```

This method returns the value of asserted parameter.

#### Services

```php
$service = $this->assertService('app.my_provider', 'Foo\Bar\Provider');
```

You can also assert service with autowired argument:

```php
$service = $this->assertAutowiredService('Foo\Bar\Provider', 'customerProvider');
```

Both methods above return the asserted service instance.

#### Private services

**In Symfony 4.1**, tests allow fetching private services by default. 
However, all **unused private services** will be removed from the container and you will get an error like below:

> The "App\SomeService" service or alias has been removed or inlined when the container
> was compiled. You should either make it public, or stop using the container directly
> and use dependency injection instead.

In others words, all unused services of your bundle must be public to be tested... 
Should we really avoid Symfony best practices? Of course not! This pack provides a trick to add your services manually. 
Thanks to that, your service is registered with flags ```autowire```, ```autoconfigure``` and... ```public```.

To do that, come back in the configuration of your kernel, and define all unused privates services you want to test:

```php
protected static function configureKernel(KernelContext $context): void
{
    $context
        // ...
        ->setService('app.my_service', 'App\SomeService') // Service with ID
        ->setService('App\SomeService'); // Service with class as ID
}
```

That's it!