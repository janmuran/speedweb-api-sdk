<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Subuser;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class SubusersResourceTest extends TestCase
{
    public function testListReturnsSubusers(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'data' => [
                ['id' => 1, 'login' => 'foo', 'name' => null, 'email' => null, 'phone' => null],
            ],
        ]));

        $subusers = $client->subusers()->list();

        self::assertCount(1, $subusers);
        self::assertInstanceOf(Subuser::class, $subusers[0]);
        self::assertSame('foo', $subusers[0]->login);
    }

    public function testListForNonMainAccountThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(403, ['message' => 'Only the main user can list subusers']));

        $this->expectException(ApiException::class);
        $client->subusers()->list();
    }
}
