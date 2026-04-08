<?php

declare(strict_types=1);

namespace App\Tests\race_condition;

use App\Photo\Application\PhotoService;
use App\Photo\Application\PhoenixPhotoImporter;
use App\Tests\Support\InMemoryPhotoRepository;
use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PhoenixPhotoImporterRaceConditionTest extends TestCase
{
    public function testRapidSequentialImportsDoNotCrashAndKeepDeterministicCount(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'photos' => [
                    ['id' => 1, 'photo_url' => 'https://example.com/1.jpg'],
                    ['id' => 2, 'photo_url' => 'https://example.com/2.jpg'],
                    ['id' => 3, 'photo_url' => 'https://example.com/3.jpg'],
                ],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'photos' => [
                    ['id' => 1, 'photo_url' => 'https://example.com/1.jpg'],
                    ['id' => 2, 'photo_url' => 'https://example.com/2.jpg'],
                    ['id' => 3, 'photo_url' => 'https://example.com/3.jpg'],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $photoRepository = new InMemoryPhotoRepository();
        $photoService = new PhotoService($photoRepository);
        $importer = new PhoenixPhotoImporter($httpClient, $photoService, 'http://phoenix:4000');

        $user = (new User())
            ->setUsername('race-user')
            ->setEmail('race@example.com')
            ->setPhoenixApiToken('race_token');

        $first = $importer->import($user);
        $second = $importer->import($user);

        self::assertSame(3, $first);
        self::assertSame(3, $second);
        self::assertCount(6, $photoRepository->findAllWithUsers());
    }
}
