<?php

namespace Ang3\Bundle\Test\Kernel;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel;

trait MicroKernelTestTrait
{
    /**
     * Kernel auto-boot.
     *
     * @var bool
     */
    protected static $autoBoot = true;

    protected function initializeBundleTest(): void
    {
        if (static::$autoBoot) {
            static::bootKernel();
        }
    }

    protected static function createKernel(array $options = []): ContextualKernel
    {
        $context = KernelContext::create();
        $context->addBundle(new FrameworkBundle());
        static::configureKernel($context);

        return $context->createKernel();
    }

    abstract protected static function configureKernel(KernelContext $context): void;
}
