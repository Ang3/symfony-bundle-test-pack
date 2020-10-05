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

            $tmpFile = $this->temporaryFile(basename($filename), $contents);
            $loader->load($tmpFile);
        }
    }

    public function shutdown(): void
    {
        $cacheDir = $this->getCacheDir();
        parent::shutdown();
        $this->filesystem->remove($cacheDir);
    }

    /**
     * @internal
     */
    private function temporaryFile(string $name, string $content): string
    {
        $filename = DIRECTORY_SEPARATOR.
            trim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).
            DIRECTORY_SEPARATOR.
            ltrim($name, DIRECTORY_SEPARATOR);

        file_put_contents($filename, $content);

        register_shutdown_function(static function () use ($filename) {
            unlink($filename);
        });

        return $filename;
    }
}
