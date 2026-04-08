<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Photo\Domain\Entity\Photo;
use App\Photo\Domain\Repository\PhotoRepositoryInterface;

final class InMemoryPhotoRepository implements PhotoRepositoryInterface
{
    /** @var list<Photo> */
    private array $photos = [];

    public function findAllWithUsers(): array
    {
        return $this->photos;
    }

    public function findAllWithUsersByFilters(array $filters): array
    {
        return array_values(array_filter(
            $this->photos,
            static function (Photo $photo) use ($filters): bool {
                if (!self::matchesStringFilter($photo->getLocation(), $filters['location'] ?? null)) {
                    return false;
                }

                if (!self::matchesStringFilter($photo->getCamera(), $filters['camera'] ?? null)) {
                    return false;
                }

                if (!self::matchesStringFilter($photo->getDescription(), $filters['description'] ?? null)) {
                    return false;
                }

                $username = $photo->getUser()->getUsername();
                if (!self::matchesStringFilter($username, $filters['username'] ?? null)) {
                    return false;
                }

                return self::matchesTakenAtFilter($photo, $filters['taken_at'] ?? null);
            }
        ));
    }

    public function findById(int $id): ?Photo
    {
        foreach ($this->photos as $photo) {
            if ($photo->getId() === $id) {
                return $photo;
            }
        }

        return null;
    }

    public function save(Photo $photo): void
    {
        $this->photos[] = $photo;
    }

    private static function matchesStringFilter(?string $value, mixed $filter): bool
    {
        if (!is_string($filter) || $filter === '') {
            return true;
        }

        if ($value === null) {
            return false;
        }

        return str_contains(mb_strtolower($value), mb_strtolower($filter));
    }

    private static function matchesTakenAtFilter(Photo $photo, mixed $takenAtFilter): bool
    {
        if (!is_string($takenAtFilter) || $takenAtFilter === '') {
            return true;
        }

        try {
            $dayStart = new \DateTimeImmutable($takenAtFilter . ' 00:00:00');
        } catch (\Throwable) {
            return true;
        }

        $dayEnd = $dayStart->modify('+1 day');
        $takenAt = $photo->getTakenAt();

        return $takenAt !== null && $takenAt >= $dayStart && $takenAt < $dayEnd;
    }
}
