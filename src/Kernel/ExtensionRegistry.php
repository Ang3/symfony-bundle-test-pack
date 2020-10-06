<?php

namespace Ang3\Bundle\Test\Kernel;

use Generator;

class ExtensionRegistry implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $extensions = [];

    public function getIterator(): Generator
    {
        foreach ($this->extensions as $name => $config) {
            yield $name => $config;
        }
    }

    public function addSwiftmailerExtension(): self
    {
        return $this->add('swiftmailer', DefaultPackageConfigs::SWIFTMAILER);
    }

    public function addApiPlatformExtension(): self
    {
        if (!isset($this->extensions['doctrine'])) {
            $this->addDoctrineExtension();
        }

        return $this->add('api_platform', DefaultPackageConfigs::API_PLATFORM);
    }

    public function addDoctrineExtension(): self
    {
        if (!isset($this->extensions['framework'])) {
            $this->addFrameworkExtension();
        }

        return $this->add('doctrine', DefaultPackageConfigs::DOCTRINE);
    }

    public function addSecurityExtension(): self
    {
        if (!isset($this->extensions['framework'])) {
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
        if (!isset($this->extensions[$name])) {
            $this->extensions[$name] = [];
        }

        $this->extensions[$name] = array_merge_recursive($this->extensions[$name], $config);

        return $this;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
