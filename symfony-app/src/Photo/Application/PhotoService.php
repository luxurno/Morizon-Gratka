<?php

namespace App\Photo\Application;

use App\Photo\Domain\Entity\Photo;
use App\Photo\Domain\Repository\PhotoRepositoryInterface;

class PhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $photoRepository
    ) { }

    public function findAllWithUsers(): array
    {
        return $this->photoRepository->findAllWithUsers();
    }

    public function findAllWithUsersByFilters(array $filters): array
    {
        return $this->photoRepository->findAllWithUsersByFilters($filters);
    }

    public function findById(int $id): ?Photo
    {
        return $this->photoRepository->findById($id);
    }

    public function save(Photo $photo): void
    {
        $this->photoRepository->save($photo);
    }
}
