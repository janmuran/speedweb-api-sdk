<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\FtpAccount;
use PHPUnit\Framework\TestCase;

final class FtpAccountTest extends TestCase
{
    public function testFromArrayToArrayRoundTrip(): void
    {
        $data = [
            'id' => 20,
            'name' => 'deploy@example.sk',
            'username' => 'deploy@example.sk',
            'dir' => 'web/',
        ];

        $account = FtpAccount::fromArray($data);

        self::assertSame($data, $account->toArray());
    }

    public function testUsernameFallsBackToNameWhenOmitted(): void
    {
        $account = FtpAccount::fromArray(['id' => 20, 'name' => 'deploy@example.sk', 'dir' => 'web/']);

        self::assertSame('deploy@example.sk', $account->username);
    }
}
