<?php

declare(strict_types=1);

namespace App\Tests\stress;

use App\Photo\Application\PhotoService;
use App\Photo\Application\PhoenixPhotoImporter;
use App\Tests\Support\InMemoryPhotoRepository;
use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PhoenixPhotoImporterStressTest extends TestCase
{
    public function testCanImportLargeBatchOfPhotos(): void
    {
        $photos = [];
        for ($i = 1; $i <= 1000; ++$i) {
            $photos[] = ['id' => $i, 'photo_url' => sprintf('https://example.com/%d.jpg', $i)];
        }

        $httpClient = new MockHttpClient([
            new MockResponse(json_encode(['photos' => $photos], JSON_THROW_ON_ERROR)),
        ]);

        $photoRepository = new InMemoryPhotoRepository();
        $photoService = new PhotoService($photoRepository);
        $importer = new PhoenixPhotoImporter($httpClient, $photoService, 'http://phoenix:4000');

        $user = (new User())
            ->setUsername('stress-user')
            ->setEmail('stress@example.com')
            ->setPhoenixApiToken('stress_token');

        $imported = $importer->import($user);

        self::assertSame(1000, $imported);
        self::assertCount(1000, $photoRepository->findAllWithUsers());
    }
}
