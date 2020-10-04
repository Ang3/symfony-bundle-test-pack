Symfony bundle test pack
========================

This pack provides tools to write functional test for reusable bundles.

No need to create a test application, or 
tons of micro kernel files: this pack provides a web test class with a contextual kernel 
that you can configure easily before using it.

**The idea:** create classes extending ```Symfony\Bundle\FrameworkBundle\Test\KernelTestCase``` 
and override the method ```KernelTestCase::createKernel()``` to create a kernel from a context instead of basic options.

Summary
=======

- [Installation](#installation)
- [Usage](#usage)
    - [Write your test](#write-your-test)
    - [Configure the kernel](#configure-the-kernel)
    - [Assertions](#assertions)
        - [Parameters and services](#parameters-and-services)

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

If you want to do a web test case, you just have to change the class ```Ang3\Bundle\Test\BundleTestCase``` 
by the class ```Ang3\Bundle\Test\WebTestCase```.

Configure the kernel
--------------------

### Disable auto-boot

By default, the kernel is automatically booted. 
However, you can disable by overriding static parameter ```$autoBoot```:

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
The bundle ```Symfony\Bundle\FrameworkBundle\FrameworkBundle``` is automatically registered, no need to add it here.

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

The bundle test class provides some useful methods to assert container parameters and services.

### Parameters

```php
$parameterValue = $this->assertParameter('app.my_param', 'foo');
```

This method returns the value of asserted parameter.

### Services

You can assert only **public** services like below:

```php
$service = $this->assertService('app.my_provider', 'Foo\Bar\Provider');
```

You can also assert service with autowired argument:

```php
$service = $this->assertAutowiredService('Foo\Bar\Provider', 'customerProvider');
```

These both methods return the asserted service instance.