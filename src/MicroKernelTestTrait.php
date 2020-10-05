<?php

namespace Ang3\Bundle\Test;

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

    protected static function createKernel(array $options = []): Kernel
    {
        $context = KernelContext::create();
        $context->addBundle(new FrameworkBundle());
        static::configureKernel($context);

        return new ContextualKernel($context);
    }

    abstract protected static function configureKernel(KernelContext $context): void;
}
