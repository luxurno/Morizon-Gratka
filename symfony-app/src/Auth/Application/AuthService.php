<?php

namespace App\Auth\Application;

use App\Auth\Infrastructure\Doctrine\AuthTokenRepository;

class AuthService
{
    public function __construct(private AuthTokenRepository $authTokenRepository)
    { }

    public function getToken(string $token): ?array
    {
        return $this->authTokenRepository->getToken($token);
    }
}