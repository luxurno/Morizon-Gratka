<?php

declare(strict_types=1);

namespace App\UI\REST\Controller\Home;

use App\Likes\Application\LikeService;
use App\Photo\Application\PhotoService;
use App\User\Application\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private LikeService $likeService,
        private PhotoService $photoService,
        private UserService $userService,
    ) { }
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $filters = [
            'location' => trim((string) $request->query->get('location', '')),
            'camera' => trim((string) $request->query->get('camera', '')),
            'description' => trim((string) $request->query->get('description', '')),
            'taken_at' => trim((string) $request->query->get('taken_at', '')),
            'username' => trim((string) $request->query->get('username', '')),
        ];

        $hasFilters = (bool) array_filter($filters, static fn (string $value): bool => $value !== '');
        $photos = $hasFilters
            ? $this->photoService->findAllWithUsersByFilters($filters)
            : $this->photoService->findAllWithUsers();

        $session = $request->getSession();
        $userId = (int) $session->get('user_id');
        $currentUser = null;
        $userLikes = [];

        if ($userId) {
            $currentUser = $this->userService->findById($userId);

            if ($currentUser) {
                foreach ($photos as $photo) {
                    $userLikes[$photo->getId()] = $this->likeService->hasUserLikedPhoto($photo, $currentUser);
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
            'filters' => $filters,
        ]);
    }
}
