<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Database;
use JanMuran\SpeedwebApiSdk\Model\DatabaseUser;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class DatabasesResourceTest extends TestCase
{
    public function testListReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [
                ['id' => 30, 'domain_id' => 1, 'nazov' => 'example_db', 'type' => 'mysql', 'quota' => null, 'memo' => null, 'server_id' => null, 'users' => []],
            ],
            'per_page' => 50,
            'total' => 1,
            'last_page' => 1,
        ]));

        $databases = $client->databases()->list(1);

        self::assertInstanceOf(PaginatedCollection::class, $databases);
        self::assertCount(1, $databases);
        self::assertInstanceOf(Database::class, $databases->items[0]);
    }

    public function testListUsersReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [
                ['id' => 40, 'db_id' => 30, 'user' => 'example_user', 'perm' => 'all', 'user_grant' => 'ALL', 'info' => null],
            ],
            'per_page' => 50,
            'total' => 1,
            'last_page' => 1,
        ]));

        $users = $client->databases()->listUsers(1, 30);

        self::assertInstanceOf(PaginatedCollection::class, $users);
        self::assertCount(1, $users);
        self::assertInstanceOf(DatabaseUser::class, $users->items[0]);
    }

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
