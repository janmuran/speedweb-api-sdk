<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Exception\ValidationException;
use JanMuran\SpeedwebApiSdk\Model\Mailbox;
use JanMuran\SpeedwebApiSdk\Model\MailboxWithSize;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateMailboxRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class MailboxesResourceTest extends TestCase
{
    public function testListReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [['id' => 10, 'name' => 'info@example.sk', 'mailroute' => null, 'mailDelivery' => true]],
            'per_page' => 50,
            'total' => 1,
            'last_page' => 1,
        ]));

        $mailboxes = $client->mailboxes()->list(1);

        self::assertInstanceOf(PaginatedCollection::class, $mailboxes);
        self::assertCount(1, $mailboxes);
        self::assertInstanceOf(Mailbox::class, $mailboxes->items[0]);
    }

    public function testCreateReturnsCreatedMailbox(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => ['id' => 10, 'name' => 'info@example.sk', 'mailroute' => null, 'mailDelivery' => true],
        ]));

        $mailbox = $client->mailboxes()->create(1, new CreateMailboxRequest(localPart: 'info', password: 'secret1'));

        self::assertInstanceOf(Mailbox::class, $mailbox);
        self::assertSame('info@example.sk', $mailbox->name);
    }

    public function testCreateWithValidationErrorThrowsValidationException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(422, [
            'message' => 'Validation error',
            'errors' => ['password' => ['The password must be at least 6 characters.']],
        ]));

        $this->expectException(ValidationException::class);
        $client->mailboxes()->create(1, new CreateMailboxRequest(localPart: 'info', password: 'secret1'));
    }

    public function testListWithSizesReturnsMailboxWithSizeModels(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => [
                ['id' => 10, 'name' => 'info@example.sk', 'mailroute' => null, 'mailDelivery' => true, 'quota' => 1024, 'quota_used' => 250.5],
            ],
        ]));

        $mailboxes = $client->mailboxes()->listWithSizes(1);

        self::assertCount(1, $mailboxes);
        self::assertSame(250.5, $mailboxes->items[0]->quotaUsed);
    }

    public function testUpdateReturnsUpdatedMailbox(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => ['id' => 10, 'name' => 'info@example.sk', 'mailroute' => null, 'mailDelivery' => true, 'quota' => 2048, 'quota_used' => 250.5],
        ]));

        $mailbox = $client->mailboxes()->update(1, 10, new UpdateMailboxRequest(quota: 2048));

        self::assertInstanceOf(MailboxWithSize::class, $mailbox);
        self::assertSame(2048, $mailbox->quota);
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(204, []));

        $client->mailboxes()->delete(1, 10);

        self::assertSame('DELETE', $mock->getLastRequest()->getMethod());
        self::assertStringEndsWith('/api/v1/domains/1/mailboxes/10', (string) $mock->getLastRequest()->getUri());
    }

    public function testDeleteNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'Mailbox not found for this domain']));

        $this->expectException(ApiException::class);
        $client->mailboxes()->delete(1, 999);
    }
}
