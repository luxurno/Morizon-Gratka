<?php

declare(strict_types=1);

namespace App\UI\REST\Controller\Profile;

use App\Photo\Application\Exception\InvalidPhoenixTokenException;
use App\Photo\Application\PhoenixPhotoImporter;
use App\User\Application\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private PhoenixPhotoImporter $phoenixPhotoImporter,
    ) { }

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request): Response
    {
        $session = $request->getSession();
        $userId = (int) $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $this->userService->findById($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/phoenix-token', name: 'profile_save_phoenix_token', methods: ['POST'])]
    public function savePhoenixToken(Request $request): Response
    {
        $session = $request->getSession();
        $userId = (int) $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $this->userService->findById($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('home');
        }

        $token = trim((string) $request->request->get('phoenix_token'));
        $user->setPhoenixApiToken($token !== '' ? $token : null);
        $this->userService->save($user);

        $this->addFlash('success', 'PhoenixApi token has been saved.');

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/import-photos', name: 'profile_import_photos', methods: ['POST'])]
    public function importPhotos(Request $request): Response
    {
        $session = $request->getSession();
        $userId = (int) $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $this->userService->findById($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('home');
        }

        try {
            $imported = $this->phoenixPhotoImporter->import($user);
            $this->addFlash('success', sprintf('Imported %d photo(s) from PhoenixApi.', $imported));
        } catch (InvalidPhoenixTokenException) {
            $this->addFlash('error', 'Niepoprawny token PhoenixApi. Sprawdź token i spróbuj ponownie.');
        } catch (\Throwable) {
            $this->addFlash('error', 'Photo import failed. Try again later.');
        }

        return $this->redirectToRoute('profile');
    }
}
