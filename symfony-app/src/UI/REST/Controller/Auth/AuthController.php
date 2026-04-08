<?php

declare(strict_types=1);

namespace App\UI\REST\Controller\Auth;

use App\Auth\Application\AuthService;
use App\User\Application\UserService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authApplication,
        private UserService $userService,
    ) { }

    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function login(string $username, string $token, Request $request): Response
    {
        $tokenData = $this->authApplication->getToken($token);

        if (!$tokenData) {
            return new Response('Invalid token', 401);
        }

        $userData = $this->userService->findByUsername($username);

        if (!$userData) {
            return new Response('User not found', 404);
        }

        $session = $request->getSession();
        $session->set('user_id', $userData['id']);
        $session->set('username', $username);

        $this->addFlash('success', 'Welcome back, ' . $username . '!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
