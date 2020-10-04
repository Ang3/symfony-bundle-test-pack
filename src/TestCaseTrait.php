<?php

namespace Ang3\Bundle\Test;

trait TestCaseTrait
{
    use ContainerAssertionsTrait;
    use MicroKernelTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeBundleTest();
    }
}
