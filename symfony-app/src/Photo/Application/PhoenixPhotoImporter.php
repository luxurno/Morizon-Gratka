<?php

declare(strict_types=1);

namespace App\Photo\Application;

use App\Photo\Application\Exception\InvalidPhoenixTokenException;
use App\Photo\Domain\Entity\Photo;
use App\User\Domain\Entity\User;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhoenixPhotoImporter
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PhotoService $photoService,
        private readonly string $phoenixBaseUrl = 'http://phoenix:4000',
    ) {
    }

    public function import(User $user): int
    {
        $token = $user->getPhoenixApiToken();

        if (!$token) {
            throw new InvalidPhoenixTokenException('PhoenixApi token is missing.');
        }

        try {
            $response = $this->httpClient->request('GET', rtrim($this->phoenixBaseUrl, '/') . '/api/photos', [
                'headers' => [
                    'access-token' => $token,
                ],
            ]);
        } catch (ExceptionInterface $exception) {
            throw new \RuntimeException('Unable to reach PhoenixApi.', previous: $exception);
        }

        if (401 === $response->getStatusCode()) {
            throw new InvalidPhoenixTokenException('Provided PhoenixApi token is invalid.');
        }

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('PhoenixApi returned unexpected status code.');
        }

        /** @var array{photos?: array<int, array{photo_url?: string}>} $payload */
        $payload = $response->toArray();
        $imported = 0;

        foreach ($payload['photos'] ?? [] as $item) {
            $photoUrl = $item['photo_url'] ?? null;
            if (!$photoUrl) {
                continue;
            }

            $photo = new Photo();
            $photo
                ->setUser($user)
                ->setImageUrl($photoUrl);

            $this->photoService->save($photo);
            ++$imported;
        }

        return $imported;
    }
}
