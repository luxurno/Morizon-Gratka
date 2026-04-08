<?php

namespace App\Likes\Application\Command;

class CreateLikeCommand
{
    public function __construct(
        public int $photoId,
        public int $userId,
    ) { }

    public function getPhotoId(): int
    {
        return $this->photoId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}