<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Exception\ValidationException;
use JanMuran\SpeedwebApiSdk\Model\DnsRecord;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class DnsResourceTest extends TestCase
{
    public function testListReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [
                ['id' => 101, 'domain_id' => 1, 'name' => 'www', 'ttl' => 3600, 'type' => 'A', 'value' => '1.2.3.4', 'memo' => null],
            ],
            'per_page' => 50,
            'total' => 1,
            'last_page' => 1,
        ]));

        $records = $client->dns()->list(1, page: 2, perPage: 50);

        self::assertInstanceOf(PaginatedCollection::class, $records);
        self::assertCount(1, $records);
        self::assertInstanceOf(DnsRecord::class, $records->items[0]);
        self::assertSame('page=2&per_page=50', $mock->getLastRequest()->getUri()->getQuery());
    }

    public function testCreateReturnsCreatedRecord(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => [
                'id' => 101, 'domain_id' => 1, 'name' => 'www', 'ttl' => 3600,
                'type' => 'A', 'value' => '1.2.3.4', 'memo' => null,
            ],
        ]));

        $record = $client->dns()->create(1, new CreateDnsRecordRequest(type: 'A', value: '1.2.3.4', ttl: 3600, name: 'www'));

        self::assertInstanceOf(DnsRecord::class, $record);
        self::assertSame(101, $record->id);
    }

    public function testCreateWithValidationErrorThrowsValidationException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(422, [
            'message' => 'Validation error',
            'errors' => ['ttl' => ['The ttl must be between 300 and 86400.']],
        ]));

        $this->expectException(ValidationException::class);
        $client->dns()->create(1, new CreateDnsRecordRequest(type: 'A', value: '1.2.3.4', ttl: 3600));
    }

    public function testCreateSendsIdempotencyKeyHeaderWhenProvided(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => [
                'id' => 101, 'domain_id' => 1, 'name' => 'www', 'ttl' => 3600,
                'type' => 'A', 'value' => '1.2.3.4', 'memo' => null,
            ],
        ]));

        $client->dns()->create(
            1,
            new CreateDnsRecordRequest(type: 'A', value: '1.2.3.4', ttl: 3600, name: 'www'),
            idempotencyKey: 'dns-abc-123',
        );

        self::assertSame('dns-abc-123', $mock->getLastRequest()->getHeaderLine('Idempotency-Key'));
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(204, []));

        $client->dns()->delete(1, 101);

        self::assertSame('DELETE', $mock->getLastRequest()->getMethod());
        self::assertStringEndsWith('/api/v1/domains/1/dns-records/101', (string) $mock->getLastRequest()->getUri());
    }

    public function testDeleteNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'DNS record not found for this domain']));

        $this->expectException(ApiException::class);
        $client->dns()->delete(1, 999);
    }
}
