<?php

namespace Ang3\Bundle\Test\Kernel;

use Generator;

class ExtensionRegistry implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $config = [];

    public function getIterator(): Generator
    {
        foreach ($this->config as $name => $config) {
            yield $name => $config;
        }
    }

    public function addSwiftmailerExtension(): self
    {
        return $this->add('swiftmailer', DefaultPackageConfigs::SWIFTMAILER);
    }

    public function addApiPlatformExtension(): self
    {
        if (!isset($this->config['doctrine'])) {
            $this->addDoctrineExtension();
        }

        return $this->add('api_platform', DefaultPackageConfigs::API_PLATFORM);
    }

    public function addDoctrineExtension(): self
    {
        if (!isset($this->config['framework'])) {
            $this->addFrameworkExtension();
        }

        return $this->add('doctrine', DefaultPackageConfigs::DOCTRINE);
    }

    public function addSecurityExtension(): self
    {
        if (!isset($this->config['framework'])) {
            $this->addFrameworkExtension();
        }

        return $this->add('security', DefaultPackageConfigs::SECURITY);
    }

    public function addFrameworkExtension(): self
    {
        return $this->add('framework', DefaultPackageConfigs::FRAMEWORK);
    }

    public function add(string $name, array $config = []): self
    {
        if (!isset($this->config[$name])) {
            $this->config[$name] = [];
        }

        $this->config[$name] = array_merge_recursive($this->config[$name], $config);

        return $this;
    }

    public function get(string $name): ?array
    {
        return $this->config[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->config[$name]);
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
