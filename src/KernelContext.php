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
     * @var Callable|null
     */
    private $container;

    /**
     * @var Callable|null
     */
    private $routing;

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

    public function addBundle(BundleInterface $bundle): self
    {
        $this->bundles[$bundle->getName()] = $bundle;

        return $this;
    }

    public function removeBundle(string $name): self
    {
        if (array_key_exists($name, $this->bundles)) {
            unset($this->bundles[$name]);
        }

        return $this;
    }

    public function hasBundle(string $name): bool
    {
        return isset($this->bundles[$name]);
    }

    public function getContainer(): ?callable
    {
        return $this->container;
    }

    public function setContainer(?callable $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getRouting(): ?callable
    {
        return $this->routing;
    }

    public function setRouting(?callable $routing): self
    {
        $this->routing = $routing;

        return $this;
    }
}
