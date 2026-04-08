<?php

declare(strict_types=1);

namespace App\Tests\smoke;

use App\Kernel;
use PHPUnit\Framework\TestCase;

final class KernelSmokeTest extends TestCase
{
    public function testKernelCanBeBooted(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();

        self::assertNotNull($kernel->getContainer());

        $kernel->shutdown();
    }
}
