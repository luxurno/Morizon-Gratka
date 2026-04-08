<?php

declare(strict_types=1);

namespace App\Tests\integration;

use App\Photo\Application\PhotoService;
use App\Photo\Application\PhoenixPhotoImporter;
use App\Tests\Support\InMemoryPhotoRepository;
use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PhoenixPhotoImporterIntegrationTest extends TestCase
{
    public function testImportsPhotosForUserFromPhoenixApiResponse(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'photos' => [
                    ['id' => 1, 'photo_url' => 'https://example.com/1.jpg'],
                    ['id' => 2, 'photo_url' => 'https://example.com/2.jpg'],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $photoRepository = new InMemoryPhotoRepository();
        $photoService = new PhotoService($photoRepository);
        $importer = new PhoenixPhotoImporter($httpClient, $photoService, 'http://phoenix:4000');

        $user = (new User())
            ->setUsername('tester')
            ->setEmail('tester@example.com')
            ->setPhoenixApiToken('valid_token');

        $imported = $importer->import($user);

        self::assertSame(2, $imported);
        self::assertCount(2, $photoRepository->findAllWithUsers());
        self::assertSame($user, $photoRepository->findAllWithUsers()[0]->getUser());
    }
}
