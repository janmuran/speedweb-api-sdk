<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\AuthenticationException;
use JanMuran\SpeedwebApiSdk\Model\Domain;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class DomainsResourceTest extends TestCase
{
    public function testListReturnsDomains(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => [
                [
                    'id' => 1, 'domain' => 'example.sk', 'server' => 'web1', 'mailserver' => 'mail1',
                    'email' => 10, 'ftp' => 122, 'db' => 10, 'quota_updated' => '2026-07-08',
                ],
            ],
        ]));

        $domains = $client->domains()->list();

        self::assertCount(1, $domains);
        self::assertInstanceOf(Domain::class, $domains[0]);
        self::assertSame('example.sk', $domains[0]->domain);
    }

    public function testListWithInvalidApiKeyThrowsAuthenticationException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(401, ['message' => 'Invalid or missing API key']));

        $this->expectException(AuthenticationException::class);
        $client->domains()->list();
    }
}
