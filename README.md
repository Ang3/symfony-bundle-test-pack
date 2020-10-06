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
    - [Configure the kernel](#configure-the-kernel)
        - [Register bundles](#register-bundles)
        - [Private services and aliases](#private-services-and-aliases)
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
use Ang3\Bundle\Test\ContextualKernel;

$context = ContextualKernel::createContext();
$kernel = new ContextualKernel($context);
```

Working with kernel context
---------------------------

By default, the kernel is just... a micro-kernel. No bundles registered. 
All the logic resides on the context to configure the kernel as to your needs.

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

### Private services and aliases

### Build the container

### Configure routing