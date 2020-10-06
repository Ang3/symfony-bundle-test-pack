<?php

namespace Ang3\Bundle\Test;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Makes enumerated private services that needs to be tested public
 * so they can be fetched from the container without a deprecation warning.
 *
 * @see https://github.com/symfony/symfony-docs/issues/8097
 * @see https://github.com/symfony/symfony/issues/24543
 */
class TestContainerPass implements CompilerPassInterface
{
    /**
     * @var string[]
     */
    private $services;

    /**
     * @var string[]
     */
    private $aliases;

    public function __construct(array $services = [], array $aliases = [])
    {
        $this->services = $services;
        $this->aliases = $aliases;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->services as $id) {
            if(!$container->hasDefinition($id)) {
                continue;
            }

            $container
                ->getDefinition($id)
                ->setPublic(true);
        }

        foreach ($this->aliases as $id) {
            if(!$container->hasAlias($id)) {
                continue;
            }

            $container
                ->getAlias($id)
                ->setPublic(true);
        }
    }
}
