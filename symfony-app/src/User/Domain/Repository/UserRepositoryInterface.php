<?php

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?array;
    public function findById(int $id): ?User;
    public function save(User $user): void;
}
