<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Http\Mock\Client as MockClient;
use JanMuran\SpeedwebApiSdk\ApiClient;
use JanMuran\SpeedwebApiSdk\ClientConfig;
use JanMuran\SpeedwebApiSdk\Http\HttpClient;
use Psr\Http\Message\ResponseInterface;

final class MockApiClientFactory
{
    /**
     * @param array<string, mixed> $configOptions
     * @return array{0: ApiClient, 1: MockClient}
     */
    public static function create(array $configOptions = []): array
    {
        $mockClient = new MockClient();

        $config = new ClientConfig(...array_merge([
            'apiKey' => 'swk_test_1234567890',
            'maxRetries' => 2,
            'retryBaseDelayMs' => 1,
            'retryMaxDelayMs' => 2,
        ], $configOptions));

        $httpClient = new HttpClient(
            $config,
            $mockClient,
            sleeper: static function (int $ms): void {
                // No real waiting in tests.
            },
        );

        return [new ApiClient($config, $httpClient), $mockClient];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public static function jsonResponse(int $status, array $data, array $headers = []): ResponseInterface
    {
        return new Response(
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers),
            json_encode($data, JSON_THROW_ON_ERROR),
        );
    }
}
