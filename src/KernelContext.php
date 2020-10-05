<?php

namespace Ang3\Bundle\Test;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class KernelContext
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var BundleInterface[]
     */
    private $bundles = [];

    /**
     * @var array
     */
    private $extensions = [];

    /**
     * @var array<string, mixed>
     */
    private $parameters = [];

    /**
     * @var string[]
     */
    private $services = [];

    /**
     * @var string[]
     */
    private $routes = [];

    public function __construct(string $environment = 'test', bool $debug = true)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    public static function create(array $config = []): self
    {
        if (!isset($config['environment'])) {
            if (isset($_ENV['APP_ENV'])) {
                $config['environment'] = $_ENV['APP_ENV'];
            } elseif (isset($_SERVER['APP_ENV'])) {
                $config['environment'] = $_SERVER['APP_ENV'];
            } else {
                $config['environment'] = 'test';
            }
        }

        if (!isset($config['debug'])) {
            if (isset($_ENV['APP_DEBUG'])) {
                $config['debug'] = $_ENV['APP_DEBUG'];
            } elseif (isset($_SERVER['APP_DEBUG'])) {
                $config['debug'] = $_SERVER['APP_DEBUG'];
            } else {
                $config['debug'] = true;
            }
        }

        $context = new self($config['environment'], $config['debug']);
        $context->setBundles($config['bundles'] ?? []);
        $context->setExtensions($config['extensions'] ?? []);
        $context->setParameters($config['parameters'] ?? []);
        $context->setServices($config['services'] ?? []);
        $context->setRoutes($config['routes'] ?? []);

        return $context;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function getBundles(): array
    {
        return $this->bundles;
    }

    public function setBundles(array $bundles): self
    {
        $this->bundles = [];

        foreach ($bundles as $bundle) {
            $this->addBundle($bundle);
        }

        return $this;
    }

    public function addBundle(BundleInterface $bundle, array $config = []): self
    {
        $this->bundles[get_class($bundle)] = $bundle;

        if($extension = $bundle->getContainerExtension()) {
            $this->setExtension($extension->getAlias(), $config);
        }

        return $this;
    }

    public function removeBundle(string $class): self
    {
        $bundle = $this->bundles[$class] ?? null;

        if($bundle) {
            $extension = $bundle->getContainerExtension();

            if($extension && isset($this->extensions[$extension->getAlias()])) {
                unset($this->extensions[$extension->getAlias()]);
            }

            unset($this->bundles[$class]);
        }

        return $this;
    }

    public function hasBundle(string $class): bool
    {
        return isset($this->bundles[$class]);
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setExtensions(array $extensions): self
    {
        $this->extensions = [];

        foreach ($extensions as $name => $config) {
            $this->setExtension($name, $config);
        }

        return $this;
    }

    public function setExtension(string $extensionName, array $config = []): self
    {
        $this->extensions[$extensionName] = $config;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = [];

        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }

        return $this;
    }

    public function setParameter(string $name, string $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function setServices(array $services): self
    {
        $this->services = [];

        foreach ($services as $name => $class) {
            $this->setService($name, $class);
        }

        return $this;
    }

    public function setService(string $name, string $class = null): self
    {
        $this->services[$name] = $class;

        return $this;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function setRoutes(array $routes): self
    {
        $this->routes = [];

        foreach ($routes as $filename) {
            $this->addRoute($filename);
        }

        return $this;
    }

    public function addRoute(string $filename): self
    {
        if (!in_array($filename, $this->routes)) {
            $this->routes[] = $filename;
        }

        return $this;
    }
}
