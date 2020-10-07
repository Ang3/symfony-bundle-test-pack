<?php

namespace Ang3\Bundle\Test\Kernel;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class KernelContext
{
    public const AUTO_PROVIDE_MISSING_EXTENSIONS = 'auto_provide_missing_extensions';

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
     * @var ExtensionRegistry
     */
    private $extensions;

    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * @var string[]
     */
    private $privateServices = [];

    /**
     * @var string[]
     */
    private $privateAliases = [];

    /**
     * @var callable|null
     */
    private $container;

    /**
     * @var callable|null
     */
    private $routing;

    /**
     * @var callable|null
     */
    private $builder;

    /**
     * @var array
     */
    private $options = [
        self::AUTO_PROVIDE_MISSING_EXTENSIONS => true,
    ];

    public function __construct(array $options = [])
    {
        if (!isset($options['environment'])) {
            if (isset($_ENV['APP_ENV'])) {
                $this->environment = (string) $_ENV['APP_ENV'];
            } elseif (isset($_SERVER['APP_ENV'])) {
                $this->environment = (string) $_SERVER['APP_ENV'];
            } else {
                $this->environment = 'test';
            }

            unset($options['debug']);
        }

        if (!isset($options['debug'])) {
            if (isset($_ENV['APP_DEBUG'])) {
                $this->debug = (bool) $_ENV['APP_DEBUG'];
            } elseif (isset($_SERVER['APP_DEBUG'])) {
                $this->debug = (bool) $_SERVER['APP_DEBUG'];
            } else {
                $this->debug = true;
            }

            unset($options['debug']);
        }

        $this->extensions = new ExtensionRegistry();
        $this->parameters = new ParameterBag();
        $this->options = array_merge($this->options, $options);
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
        $bundleName = $bundle->getName();

        if (array_key_exists($bundleName, $this->bundles)) {
            throw new \LogicException(sprintf('The bundle "%s" was already added.', $bundleName));
        }

        $this->bundles[$bundleName] = $bundle;

        if ($config) {
            $extension = $bundle->getContainerExtension();

            if (!$extension) {
                throw new \LogicException(sprintf('The bundle "%s" has no extension to configure.', $bundleName));
            }

            $this->extensions->add($extension->getAlias(), $config);
        }

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

    public function getExtensions(): ExtensionRegistry
    {
        return $this->extensions;
    }

    public function setExtensions(ExtensionRegistry $extensions): self
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setParameter(string $name, $value): self
    {
        $this->parameters->set($name, $value);

        return $this;
    }

    public function removeParameter(string $name): self
    {
        $this->parameters->remove($name);

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters->all();
    }

    public function getPrivateServices(): array
    {
        return $this->privateServices;
    }

    public function setPrivateServices(array $privateServices = []): self
    {
        $this->privateServices = [];

        foreach ($privateServices as $service) {
            $this->setPrivateService($service);
        }

        return $this;
    }

    public function setPrivateService(string $id): self
    {
        if (!in_array($id, $this->privateServices)) {
            $this->privateServices[] = $id;
        }

        return $this;
    }

    public function getPrivateAliases(): array
    {
        return $this->privateAliases;
    }

    public function setPrivateAliases(array $privateAliases = []): self
    {
        $this->privateAliases = [];

        foreach ($privateAliases as $alias) {
            $this->setPrivateAlias($alias);
        }

        return $this;
    }

    public function setPrivateAlias(string $id): self
    {
        if (!in_array($id, $this->privateAliases)) {
            $this->privateAliases[] = $id;
        }

        return $this;
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

    public function getBuilder(): ?callable
    {
        return $this->builder;
    }

    public function setBuilder(?callable $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function autoProvideMissingExtensions(): bool
    {
        return true === $this->options[self::AUTO_PROVIDE_MISSING_EXTENSIONS];
    }

    public function createKernel(): ContextualKernel
    {
        return new ContextualKernel($this);
    }
}
