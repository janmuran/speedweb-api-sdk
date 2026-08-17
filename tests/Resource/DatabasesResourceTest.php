<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class DatabasesResourceTest extends TestCase
{
    public function testChangeUserPasswordSendsPatchWithJsonBody(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['message' => 'Password changed.']));

        $client->databases()->changeUserPassword(1, 30, 40, new ChangeDatabaseUserPasswordRequest('newpassword'));

        $request = $mock->getLastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            '/api/v1/domains/1/databases/30/users/40/password',
            $request->getUri()->getPath(),
        );
        self::assertSame(['password' => 'newpassword'], json_decode((string) $request->getBody(), true));
    }

    public function testChangeUserPasswordNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'Database or user not found for this domain']));

        $this->expectException(ApiException::class);
        $client->databases()->changeUserPassword(1, 30, 999, new ChangeDatabaseUserPasswordRequest('newpassword'));
    }
}
