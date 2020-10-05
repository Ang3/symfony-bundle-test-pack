<?php

namespace Ang3\Bundle\Test;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ContextualKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var KernelContext
     */
    private $context;

    public function __construct(KernelContext $context = null)
    {
        $this->context = $context ?: new KernelContext();
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
}
