<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\FtpAccount;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeFtpAccountPasswordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class FtpResourceTest extends TestCase
{
    public function testListReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [['id' => 20, 'name' => 'deploy@example.sk', 'username' => 'deploy@example.sk', 'dir' => 'web/']],
            'per_page' => 50,
            'total' => 1,
            'last_page' => 1,
        ]));

        $accounts = $client->ftp()->list(1);

        self::assertInstanceOf(PaginatedCollection::class, $accounts);
        self::assertCount(1, $accounts);
        self::assertInstanceOf(FtpAccount::class, $accounts->items[0]);
    }

    public function testCreateReturnsCreatedAccount(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => ['id' => 20, 'name' => 'deploy@example.sk', 'username' => 'deploy@example.sk', 'dir' => 'web/'],
        ]));

        $account = $client->ftp()->create(1, new CreateFtpAccountRequest(name: 'deploy', password: 'secret1', dir: 'web/'));

        self::assertInstanceOf(FtpAccount::class, $account);
        self::assertSame('deploy@example.sk', $account->name);
        self::assertSame('deploy@example.sk', $account->username);
    }

    public function testCreateForForeignDomainThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(403, ['message' => 'Domain does not belong to the customer']));

        $this->expectException(ApiException::class);
        $client->ftp()->create(999, new CreateFtpAccountRequest(name: 'deploy', password: 'secret1', dir: 'web/'));
    }

    public function testCreateSendsIdempotencyKeyHeaderWhenProvided(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => ['id' => 20, 'name' => 'deploy@example.sk', 'dir' => 'web/'],
        ]));

        $client->ftp()->create(
            1,
            new CreateFtpAccountRequest(name: 'deploy', password: 'secret1', dir: 'web/'),
            idempotencyKey: 'abc-123',
        );

        self::assertSame('abc-123', $mock->getLastRequest()->getHeaderLine('Idempotency-Key'));
    }

    public function testChangePasswordSendsPatchRequest(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['message' => 'Password changed.']));

        $client->ftp()->changePassword(1, 20, new ChangeFtpAccountPasswordRequest('newpassword'));

        self::assertSame('PATCH', $mock->getLastRequest()->getMethod());
        self::assertStringEndsWith('/api/v1/domains/1/ftp-accounts/20/password', (string) $mock->getLastRequest()->getUri());
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(204, []));

        $client->ftp()->delete(1, 20);

        self::assertSame('DELETE', $mock->getLastRequest()->getMethod());
        self::assertStringEndsWith('/api/v1/domains/1/ftp-accounts/20', (string) $mock->getLastRequest()->getUri());
    }

    public function testDeleteNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'FTP account not found for this domain']));

        $this->expectException(ApiException::class);
        $client->ftp()->delete(1, 999);
    }
}
