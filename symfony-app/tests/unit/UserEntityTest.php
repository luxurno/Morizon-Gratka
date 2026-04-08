<?php

declare(strict_types=1);

namespace App\Tests\unit;

use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserEntityTest extends TestCase
{
    public function testPhoenixApiTokenGetterAndSetter(): void
    {
        $user = new User();

        self::assertNull($user->getPhoenixApiToken());

        $user->setPhoenixApiToken('token_123');
        self::assertSame('token_123', $user->getPhoenixApiToken());

        $user->setPhoenixApiToken(null);
        self::assertNull($user->getPhoenixApiToken());
    }
}
