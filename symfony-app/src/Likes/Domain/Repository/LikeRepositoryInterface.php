<?php
declare(strict_types=1);

namespace App\Likes\Domain\Repository;

use App\Likes\Domain\Entity\Like;
use App\Photo\Domain\Entity\Photo;
use App\User\Domain\Entity\User;

interface LikeRepositoryInterface
{
    public function unlikePhoto(Photo $photo, User $user): void;

    public function hasUserLikedPhoto(Photo $photo, User $user): bool;

    public function createLike(Photo $photo, User $user): Like;

    public function updatePhotoCounter(Photo $photo, int $increment): void;
}