<?php

namespace Ang3\Bundle\Test;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

trait MicroKernelTestTrait
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Kernel auto-boot.
     *
     * @var bool
     */
    protected static $autoBoot = true;

    protected function initializeBundleTest(): void
    {
        $this->filesystem = new Filesystem();

        if (static::$autoBoot) {
            static::bootKernel();
        }
    }

    protected function tearDown(): void
    {
        if (!parent::$booted) {
            return;
        }

        $this->filesystem->remove(static::$kernel->getCacheDir());
        parent::tearDown();
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
