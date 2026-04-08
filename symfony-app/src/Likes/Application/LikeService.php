<?php

declare(strict_types=1);

namespace App\Likes\Application;

use App\Likes\Domain\Repository\LikeRepositoryInterface;
use App\Photo\Domain\Entity\Photo;
use App\User\Domain\Entity\User;
use Psr\Log\LoggerInterface;

class LikeService
{
    public function __construct(
        private readonly LikeRepositoryInterface $likeRepository,
    ) { }

    public function hasUserLikedPhoto(Photo $photo, User $user): bool
    {
        return $this->likeRepository->hasUserLikedPhoto($photo, $user);
    }
}
