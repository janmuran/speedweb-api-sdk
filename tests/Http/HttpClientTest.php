<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Http;

use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\NetworkException as MockNetworkException;
use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Exception\AuthenticationException;
use JanMuran\SpeedwebApiSdk\Exception\NetworkException;
use JanMuran\SpeedwebApiSdk\Exception\RateLimitException;
use JanMuran\SpeedwebApiSdk\Exception\ValidationException;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class HttpClientTest extends TestCase
{
    public function testSuccessfulRequestDecodesJsonAndSendsBearerAuth(): void
    {
        [$client, $mock] = MockApiClientFactory::create(['apiKey' => 'swk_abcdef123456']);
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => [['id' => 1, 'login' => 'foo', 'name' => null, 'email' => null, 'phone' => null]],
        ]));

        $client->subusers()->list();

        $request = $mock->getLastRequest();
        self::assertSame('Bearer swk_abcdef123456', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
    }

    public function testRetriesOnRetryableStatusThenSucceeds(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(new Response(503));
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => []]));

        $result = $client->domains()->list();

        self::assertSame([], $result);
        self::assertCount(2, $mock->getRequests());
    }

    public function testRetriesOnNetworkExceptionThenSucceeds(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addException(new MockNetworkException('connection refused', self::dummyRequest()));
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => []]));

        $result = $client->domains()->list();

        self::assertSame([], $result);
    }

    public function testExhaustedNetworkErrorRetriesThrowNetworkException(): void
    {
        [$client, $mock] = MockApiClientFactory::create(['maxRetries' => 1]);
        $mock->addException(new MockNetworkException('connection refused', self::dummyRequest()));
        $mock->addException(new MockNetworkException('connection refused', self::dummyRequest()));

        $this->expectException(NetworkException::class);
        $client->domains()->list();
    }

    public function testExhaustedStatusRetriesThrowApiExceptionForLastResponse(): void
    {
        [$client, $mock] = MockApiClientFactory::create(['maxRetries' => 1]);
        $mock->addResponse(new Response(500));
        $mock->addResponse(new Response(500));

        try {
            $client->domains()->list();
            self::fail('Expected ApiException.');
        } catch (ApiException $e) {
            self::assertSame(500, $e->getStatusCode());
        }

        // maxRetries=1 => 2 total attempts.
        self::assertCount(2, $mock->getRequests());
    }

    public function test401ThrowsAuthenticationException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(401, ['message' => 'Invalid or missing API key']));

        try {
            $client->domains()->list();
            self::fail('Expected AuthenticationException.');
        } catch (AuthenticationException $e) {
            self::assertSame(401, $e->getStatusCode());
            self::assertSame('Invalid or missing API key', $e->getMessage());
        }
    }

    public function test422ThrowsValidationExceptionWithErrors(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(422, [
            'message' => 'Validation error',
            'errors' => ['password' => ['The password must be at least 6 characters.']],
        ]));

        try {
            $client->domains()->list();
            self::fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            self::assertSame(422, $e->getStatusCode());
            self::assertSame(['password' => ['The password must be at least 6 characters.']], $e->getErrors());
        }
    }

    public function test429ThrowsRateLimitExceptionWithRetryAfter(): void
    {
        // maxRetries=0 keeps this test focused on the terminal 429 handling;
        // retrying on 429 is covered by testRetriesOnRetryableStatusThenSucceeds.
        [$client, $mock] = MockApiClientFactory::create(['maxRetries' => 0]);
        $mock->addResponse(MockApiClientFactory::jsonResponse(429, ['message' => 'Too many requests'], ['Retry-After' => '30']));

        try {
            $client->domains()->list();
            self::fail('Expected RateLimitException.');
        } catch (RateLimitException $e) {
            self::assertSame(429, $e->getStatusCode());
            self::assertSame(30, $e->getRetryAfterSeconds());
        }
    }

    public function testNonRetryableClientErrorThrowsApiExceptionWithoutRetry(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(403, ['message' => 'Domain does not belong to the customer']));

        try {
            $client->domains()->list();
            self::fail('Expected ApiException.');
        } catch (ApiException $e) {
            self::assertSame(403, $e->getStatusCode());
        }

        self::assertCount(1, $mock->getRequests());
    }

    private static function dummyRequest(): \Psr\Http\Message\RequestInterface
    {
        return new \GuzzleHttp\Psr7\Request('GET', 'https://api.speedweb.sk/api/v1/domains');
    }
}
