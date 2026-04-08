<?php

namespace App\Likes\Application\CommandHandler;

use App\Likes\Application\Command\CreateLikeCommand;
use App\Likes\Domain\Repository\LikeRepositoryInterface;
use App\Photo\Domain\Entity\Photo;
use App\Photo\Infrastructure\Doctrine\PhotoRepository;
use App\SharedKernel\CommandHandler\CommandHandlerInterface;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Doctrine\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateLikeCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository,
        private LoggerInterface $logger,
        private PhotoRepository $photoRepository,
        private UserRepository $userRepository,
    ) { }

    public function __invoke(CreateLikeCommand $command)
    {
        /** @var Photo $photo */
        $photo = $this->photoRepository->findOneBy(['id' => $command->getPhotoId()]);
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $command->getUserId()]);

        try {
            $this->likeRepository->createLike($photo, $user);
            $this->likeRepository->updatePhotoCounter($photo, 1);
        } catch (\Throwable $e) {
            $this->logger->error('Something went wrong while liking the photo: '. $e->getMessage());

            throw new \Exception('Something went wrong while liking the photo');
        }
    }
}