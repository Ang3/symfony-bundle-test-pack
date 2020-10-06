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

    public function __construct(array $services = [])
    {
        $this->services = $services;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->services as $id) {
            $container
                ->getDefinition($id)
                ->setPublic(true);
        }
    }
}
