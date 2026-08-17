<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Http;

use JanMuran\SpeedwebApiSdk\Tests\Support\CollectingLogger;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class LoggingTest extends TestCase
{
    public function testLoggingDisabledByDefaultRecordsNothing(): void
    {
        $logger = new CollectingLogger();
        [$client, $mock] = MockApiClientFactory::create(['logger' => $logger]);
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => []]));

        $client->domains()->list();

        self::assertSame([], $logger->records);
    }

    public function testLoggingEnabledRecordsRequestAndResponse(): void
    {
        $logger = new CollectingLogger();
        [$client, $mock] = MockApiClientFactory::create(['loggingEnabled' => true, 'logger' => $logger]);
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => []]));

        $client->domains()->list();

        $messages = array_column($logger->records, 'message');
        self::assertNotEmpty(array_filter($messages, static fn (string $m): bool => str_contains($m, '-> GET')));
        self::assertNotEmpty(array_filter($messages, static fn (string $m): bool => str_contains($m, '<- GET') && str_contains($m, '=> 200')));
    }

    public function testAuthorizationHeaderIsMaskedInLogs(): void
    {
        $logger = new CollectingLogger();
        [$client, $mock] = MockApiClientFactory::create([
            'apiKey' => 'swk_supersecretlongkey',
            'loggingEnabled' => true,
            'logger' => $logger,
        ]);
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => []]));

        $client->domains()->list();

        $requestLog = current(array_filter($logger->records, static fn (array $r): bool => str_contains($r['message'], '-> GET')));
        self::assertNotFalse($requestLog);

        $loggedAuthHeader = $requestLog['context']['headers']['Authorization'][0] ?? '';
        self::assertStringNotContainsString('supersecretlongkey', $loggedAuthHeader);
        self::assertStringContainsString('***', $loggedAuthHeader);
    }
}
