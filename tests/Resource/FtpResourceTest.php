<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\FtpAccount;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class FtpResourceTest extends TestCase
{
    public function testCreateReturnsCreatedAccount(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => ['id' => 20, 'name' => 'deploy@example.sk', 'dir' => 'web/'],
        ]));

        $account = $client->ftp()->create(1, new CreateFtpAccountRequest(name: 'deploy', password: 'secret1', dir: 'web/'));

        self::assertInstanceOf(FtpAccount::class, $account);
        self::assertSame('deploy@example.sk', $account->name);
    }

    public function testCreateForForeignDomainThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(403, ['message' => 'Domain does not belong to the customer']));

        $this->expectException(ApiException::class);
        $client->ftp()->create(999, new CreateFtpAccountRequest(name: 'deploy', password: 'secret1', dir: 'web/'));
    }
}
