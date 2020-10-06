<?php

namespace Ang3\Bundle\Test;

use Ang3\Bundle\Test\Assertions\ContainerAssertionsTrait;
use Ang3\Bundle\Test\Kernel\MicroKernelTestTrait;

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
