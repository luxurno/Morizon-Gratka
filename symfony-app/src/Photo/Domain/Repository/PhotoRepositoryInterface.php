<?php

namespace App\Photo\Domain\Repository;

use App\Photo\Domain\Entity\Photo;

interface PhotoRepositoryInterface
{
    public function findAllWithUsers(): array;
    public function findAllWithUsersByFilters(array $filters): array;
    public function findById(int $id): ?Photo;
    public function save(Photo $photo): void;
}
