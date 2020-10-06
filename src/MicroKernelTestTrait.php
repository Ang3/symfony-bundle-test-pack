<?php

namespace Ang3\Bundle\Test;

use Ang3\Bundle\Test\Kernel\ContextualKernel;
use Ang3\Bundle\Test\Kernel\KernelContext;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

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
        $context = ContextualKernel::createContext();
        $context
            ->addBundle(new FrameworkBundle())
            ->getExtensions()
            ->addFrameworkExtension();

        static::configureKernel($context);

        return $context->createKernel();
    }

    abstract protected static function configureKernel(KernelContext $context): void;
}
