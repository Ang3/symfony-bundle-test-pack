<?php

namespace Ang3\Bundle\Test;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ContextualKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var KernelContext
     */
    private $context;

    /**
     * @var string[]
     */
    private $tmpFiles = [];

    public function __construct(KernelContext $context = null)
    {
        $this->context = $context ?: new KernelContext();
        $this->filesystem = new Filesystem();

        parent::__construct($context->getEnvironment(), $context->isDebug());
    }

    public function registerBundles(): array
    {
        return $this->context->getBundles();
    }

    public function configureRoutes(RoutingConfigurator $routeConfigurator): void
    {
        $callback = $this->context->getRouting();

        if($callback) {
            $callback($routeConfigurator);
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $callback = $this->context->getContainer();

        if($callback) {
            $callback($container);
        }
    }

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TestContainerPass(), PassConfig::TYPE_OPTIMIZE);
    }

    public function shutdown(): void
    {
        $cacheDir = $this->getCacheDir();
        parent::shutdown();
        $this->filesystem->remove(array_merge([$cacheDir], $this->tmpFiles));
    }
}
