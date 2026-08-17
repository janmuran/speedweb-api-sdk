<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ValidationException;
use JanMuran\SpeedwebApiSdk\Model\Mailbox;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class MailboxesResourceTest extends TestCase
{
    public function testCreateReturnsCreatedMailbox(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, [
            'data' => ['id' => 10, 'name' => 'info@example.sk', 'mailroute' => null, 'mailDelivery' => true],
        ]));

        $mailbox = $client->mailboxes()->create(1, new CreateMailboxRequest(email: 'info', password: 'secret1'));

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
        $client->mailboxes()->create(1, new CreateMailboxRequest(email: 'info', password: 'secret1'));
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
        self::assertSame(250.5, $mailboxes[0]->quotaUsed);
    }
}
