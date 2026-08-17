<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\Database;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function testFromArrayToArrayRoundTripWithNestedUsers(): void
    {
        $data = [
            'id' => 30,
            'domain_id' => 1,
            'nazov' => 'example_db',
            'type' => 'mysql',
            'quota' => 500,
            'memo' => null,
            'server_id' => null,
            'users' => [
                [
                    'id' => 40,
                    'db_id' => 30,
                    'user' => 'example_user',
                    'perm' => 'all',
                    'user_grant' => 'ALL',
                    'info' => null,
                ],
            ],
        ];

        $database = Database::fromArray($data);

        self::assertCount(1, $database->users);
        self::assertSame('example_user', $database->users[0]->user);
        self::assertSame($data, $database->toArray());
    }
}
