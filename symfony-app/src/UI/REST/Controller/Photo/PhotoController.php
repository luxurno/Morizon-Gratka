<?php

declare(strict_types=1);

namespace App\UI\REST\Controller\Photo;

use App\Likes\Application\Command\CreateLikeCommand;
use App\Likes\Application\LikeService;
use App\Likes\Infrastructure\Doctrine\LikeRepository;
use App\Photo\Application\PhotoService;
use App\Photo\Domain\Entity\Photo;
use App\User\Application\UserService;
use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private LikeService $likeService,
        private PhotoService $photoService,
        private UserService $userService,
    ) { }

    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function like($id, Request $request, ManagerRegistry $managerRegistry): Response
    {
        $likeRepository = new LikeRepository($managerRegistry);

        $session = $request->getSession();
        $userId = (int) $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'You must be logged in to like photos.');
            return $this->redirectToRoute('home');
        }

        $photo = $this->photoService->findById($id);
        $user = $this->userService->findById($userId);

        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        if ($likeRepository->hasUserLikedPhoto($photo, $user)) {
            $likeRepository->unlikePhoto($photo, $user);
            $this->addFlash('info', 'Photo unliked!');
        } else {
            $this->commandBus->dispatch(new CreateLikeCommand($photo->getId(), $user->getId()));
            $this->addFlash('success', 'Photo liked!');
        }

        return $this->redirectToRoute('home');
    }
}
