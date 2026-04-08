<?php

namespace App\Auth\Infrastructure\Doctrine;

use App\Auth\Domain\Entity\AuthToken;
use App\Auth\Domain\Repository\AuthTokenRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class AuthTokenRepository extends ServiceEntityRepository implements AuthTokenRepositoryInterface
{
    public function __construct(
        protected ManagerRegistry $registry,
        private Connection $connection
    ) {
        parent::__construct($registry, AuthToken::class);
    }

    public function getToken(string $token): ?array
    {
        $sql = sprintf(
            "SELECT * FROM auth_tokens WHERE token = %s",
            $this->connection->quote($token),
        );
        $result = $this->connection->executeQuery($sql);

        return $result->fetchAssociative();
    }
}