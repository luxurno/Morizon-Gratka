<?php

declare(strict_types=1);

namespace App\Tests\extra;

use App\Photo\Application\Exception\InvalidPhoenixTokenException;
use App\Photo\Application\PhotoService;
use App\Photo\Application\PhoenixPhotoImporter;
use App\Tests\Support\InMemoryPhotoRepository;
use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PhoenixPhotoImporterInvalidTokenTest extends TestCase
{
    public function testThrowsDomainExceptionWhenPhoenixReturnsUnauthorized(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(
                json_encode(['errors' => ['detail' => 'Unauthorized']], JSON_THROW_ON_ERROR),
                ['http_code' => 401]
            ),
        ]);

        $photoService = new PhotoService(new InMemoryPhotoRepository());
        $importer = new PhoenixPhotoImporter($httpClient, $photoService, 'http://phoenix:4000');

        $user = (new User())
            ->setUsername('invalid-user')
            ->setEmail('invalid@example.com')
            ->setPhoenixApiToken('invalid_token');

        $this->expectException(InvalidPhoenixTokenException::class);
        $importer->import($user);
    }
}
