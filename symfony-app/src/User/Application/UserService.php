<?php

namespace App\User\Application;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;

class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    { }

    public function findByUsername(string $username): ?array
    {
        return $this->userRepository->findByUsername($username);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }
}