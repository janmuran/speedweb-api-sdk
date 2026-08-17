<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ValidationException;
use JanMuran\SpeedwebApiSdk\Model\DnsRecord;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class DnsResourceTest extends TestCase
{
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
}
