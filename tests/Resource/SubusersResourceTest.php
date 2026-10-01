<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Request\AssignSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\RemoveSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Subuser;
use JanMuran\SpeedwebApiSdk\Model\SubuserDomain;
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

    private static function subuserDomainData(): array
    {
        return [
            'id' => 10, 'domain_id' => 1, 'domain' => 'example.sk', 'web' => 1, 'ftp' => 1,
            'email' => 1, 'db' => 1, 'dns' => 1, 'edit' => 1, 'perm_modules' => 'ALL',
        ];
    }

    public function testListDomainsReturnsSubuserDomains(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => [self::subuserDomainData()]]));

        $domains = $client->subusers()->listDomains(5);

        self::assertCount(1, $domains);
        self::assertInstanceOf(SubuserDomain::class, $domains[0]);
        self::assertSame('example.sk', $domains[0]->domain);
    }

    public function testAssignDomainReturnsCreatedAssignment(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, ['data' => self::subuserDomainData()]));

        $assignment = $client->subusers()->assignDomain(5, new AssignSubuserDomainRequest(domain: 'example.sk'));

        self::assertInstanceOf(SubuserDomain::class, $assignment);
        self::assertSame('POST', $mock->getLastRequest()->getMethod());
    }

    public function testUnassignDomainSendsDeleteRequestWithBody(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(204, []));

        $client->subusers()->unassignDomain(5, new RemoveSubuserDomainRequest(domain: 'example.sk'));

        self::assertSame('DELETE', $mock->getLastRequest()->getMethod());
        self::assertSame('{"domain":"example.sk"}', (string) $mock->getLastRequest()->getBody());
    }
}
