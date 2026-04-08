<?php

declare(strict_types=1);

namespace App\Photo\Infrastructure\Doctrine;

use App\Photo\Domain\Entity\Photo;
use App\Photo\Domain\Repository\PhotoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository implements PhotoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithUsersByFilters(array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC');

        if (!empty($filters['location'])) {
            $queryBuilder
                ->andWhere('LOWER(p.location) LIKE :location')
                ->setParameter('location', '%' . mb_strtolower((string) $filters['location']) . '%');
        }

        if (!empty($filters['camera'])) {
            $queryBuilder
                ->andWhere('LOWER(p.camera) LIKE :camera')
                ->setParameter('camera', '%' . mb_strtolower((string) $filters['camera']) . '%');
        }

        if (!empty($filters['description'])) {
            $queryBuilder
                ->andWhere('LOWER(p.description) LIKE :description')
                ->setParameter('description', '%' . mb_strtolower((string) $filters['description']) . '%');
        }

        if (!empty($filters['username'])) {
            $queryBuilder
                ->andWhere('LOWER(u.username) LIKE :username')
                ->setParameter('username', '%' . mb_strtolower((string) $filters['username']) . '%');
        }

        if (!empty($filters['taken_at'])) {
            try {
                $dayStart = new \DateTimeImmutable((string) $filters['taken_at'] . ' 00:00:00');
                $dayEnd = $dayStart->modify('+1 day');

                $queryBuilder
                    ->andWhere('p.takenAt >= :takenAtStart')
                    ->andWhere('p.takenAt < :takenAtEnd')
                    ->setParameter('takenAtStart', $dayStart)
                    ->setParameter('takenAtEnd', $dayEnd);
            } catch (\Throwable) {
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findById(int $id): ?Photo
    {
        return $this->find($id);
    }

    public function save(Photo $photo): void
    {
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }
}
