<?php

namespace Ang3\Bundle\Test;

trait ContainerAssertionsTrait
{
    protected function assertParameters(array $parameters = []): void
    {
        foreach ($parameters as $name => $value) {
            $this->assertParameter($name, $value);
        }
    }

    /**
     * @param mixed|null $value
     *
     * @return mixed
     */
    protected function assertParameter(string $name, $value = null)
    {
        $this->assertTrue(static::$container->hasParameter($name), sprintf('The container should contain the parameter "%s"', $name));
        $parameter = static::$container->getParameter($name);

        if ($value) {
            $this->assertEquals($value, $parameter, sprintf('The value of container parameter "%s" is invalid.', $name));
        }

        return $parameter;
    }

    protected function assertAutowiredService(string $class, string $argumentName): object
    {
        $id = $this->getAutowiredArgumentName($class, $argumentName);

        return $this->assertService($id, $class);
    }

    protected function assertService(string $id, string $class = null): object
    {
        $this->assertTrue(static::$container->has($id), sprintf('The container should contain a service with ID "%s"', $id));
        $service = static::$container->get($id);

        if ($class) {
            $this->assertInstanceOf($class, $service, sprintf('The service with ID "%s" should be an instance of "%s"', $id, $class));
        }

        return $service;
    }

    protected function getAutowiredArgumentName(string $class, string $argumentName): string
    {
        return sprintf('%s $%s', $class, $argumentName);
    }
}
