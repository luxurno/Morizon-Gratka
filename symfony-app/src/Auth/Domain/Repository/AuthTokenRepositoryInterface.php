<?php

namespace App\Auth\Domain\Repository;

interface AuthTokenRepositoryInterface
{
    public function getToken(string $token): ?array;
}
