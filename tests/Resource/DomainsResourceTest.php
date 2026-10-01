<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\AuthenticationException;
use JanMuran\SpeedwebApiSdk\Model\Domain;
use JanMuran\SpeedwebApiSdk\Model\DomainDnssec;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\SetDnssecRequest;
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

        self::assertInstanceOf(PaginatedCollection::class, $domains);
        self::assertCount(1, $domains);
        self::assertInstanceOf(Domain::class, $domains->items[0]);
        self::assertSame('example.sk', $domains->items[0]->domain);
    }

    public function testListWithInvalidApiKeyThrowsAuthenticationException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(401, ['message' => 'Invalid or missing API key']));

        $this->expectException(AuthenticationException::class);
        $client->domains()->list();
    }

    public function testGetDnssecReturnsStatus(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => ['enabled' => true, 'status' => 1, 'last_signed' => '2026-01-01 12:00:00', 'dsset' => 'base64=='],
        ]));

        $dnssec = $client->domains()->getDnssec(1);

        self::assertInstanceOf(DomainDnssec::class, $dnssec);
        self::assertTrue($dnssec->enabled);
    }

    public function testSetDnssecSendsPutRequest(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => ['enabled' => false, 'status' => null, 'last_signed' => null, 'dsset' => null],
        ]));

        $dnssec = $client->domains()->setDnssec(1, new SetDnssecRequest(enabled: false));

        self::assertFalse($dnssec->enabled);
        self::assertSame('PUT', $mock->getLastRequest()->getMethod());
        self::assertStringEndsWith('/api/v1/domains/1/dnssec', (string) $mock->getLastRequest()->getUri());
    }
}
