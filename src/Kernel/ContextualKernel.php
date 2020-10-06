<?php

namespace Ang3\Bundle\Test\Kernel;

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

    public static function createContext(): KernelContext
    {
        return new KernelContext();
    }

    public function registerBundles(): array
    {
        return $this->context->getBundles();
    }

    public function configureRoutes(RoutingConfigurator $routeConfigurator): void
    {
        if ($callback = $this->context->getRouting()) {
            $callback($routeConfigurator);
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $extensions = $this->context->getExtensions();

        foreach ($this->context->getExtensions() as $name => $config) {
            $container->extension($name, $config);
        }

        if ($callback = $this->context->getContainer()) {
            $callback($container);
        }

        if ($this->context->hasBundle('FrameworkBundle') && !$extensions->has('framework')) {
            $extensions->addFrameworkExtension();
        }

        if ($this->context->hasBundle('SecurityBundle') && !$extensions->has('security')) {
            $extensions->addSecurityExtension();
        }

        if ($this->context->hasBundle('DoctrineBundle') && !$extensions->has('doctrine')) {
            $extensions->addDoctrineExtension();
        }

        if ($this->context->hasBundle('ApiPlatformBundle') && !$extensions->has('api_platform')) {
            $extensions->addApiPlatformExtension();
        }

        if ($this->context->hasBundle('SwiftmailerBundle') && !$extensions->has('swiftmailer')) {
            $extensions->addSwiftmailerExtension();
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $testContainerPass = new TestContainerPass($this->context->getPrivateServices(), $this->context->getPrivateAliases());
        $container->addCompilerPass($testContainerPass, PassConfig::TYPE_OPTIMIZE);

        if ($callback = $this->context->getBuilder()) {
            $callback($container);
        }
    }

    public function shutdown(): void
    {
        $cacheDir = $this->getCacheDir();
        parent::shutdown();
        $this->filesystem->remove(array_merge([$cacheDir], $this->tmpFiles));
    }
}
