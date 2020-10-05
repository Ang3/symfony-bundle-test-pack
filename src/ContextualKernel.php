<?php

namespace Ang3\Bundle\Test;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
        foreach ($this->context->getRoutes() as $filename) {
            $routeConfigurator->import($filename);
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        foreach ($this->context->getExtensions() as $extensionName => $config) {
            $container->extension($extensionName, (array) $config);
        }

        $containerParameters = $container->parameters();
        $containerParameters->set('kernel.default_locale', 'en_US');
        foreach ($this->context->getParameters() as $name => $value) {
            $containerParameters->set($name, $value);
        }

        $containerServices = $container->services();
        $containerServices->defaults()->public();
        foreach ($this->context->getServices() as $id => $class) {
            $containerServices->set($id, $class)->public();
        }
    }

    protected function prepareContainer(ContainerBuilder $container): void
    {
        parent::prepareContainer($container);

        $loader = new YamlFileLoader($container, new FileLocator(sys_get_temp_dir()));

        foreach ($this->context->getResources() as $filename) {
            $contents = file_get_contents($filename);

            if (!$contents) {
                throw new \RuntimeException(sprintf('Failed to load resource "%s"', $filename));
            }

            $filename = $this->temporaryFile($contents);
            $loader->load($filename);
        }
    }

    public function shutdown(): void
    {
        $cacheDir = $this->getCacheDir();
        parent::shutdown();
        $this->filesystem->remove(array_merge([$cacheDir], $this->tmpFiles));
    }

    /**
     * @internal
     */
    private function temporaryFile(string $contents): string
    {
        $tmpfile = tmpfile();
        if (false === $tmpfile) {
            throw new \RuntimeException('Failed to create a new temporary file.');
        }

        $filename = stream_get_meta_data($tmpfile)['uri'];
        file_put_contents($filename, $contents);

        return $filename;
    }
}
