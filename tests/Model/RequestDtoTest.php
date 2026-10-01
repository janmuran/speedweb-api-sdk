<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\Request\AssignSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeFtpAccountPasswordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateBillingServiceRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\RemoveSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\SetDnssecRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateMailboxRequest;
use PHPUnit\Framework\TestCase;

final class RequestDtoTest extends TestCase
{
    public function testCreateDnsRecordRoundTripOmitsNullOptionalFields(): void
    {
        $request = new CreateDnsRecordRequest(type: 'A', value: '1.2.3.4', ttl: 3600);

        self::assertSame(['type' => 'A', 'value' => '1.2.3.4', 'ttl' => 3600], $request->toArray());
    }

    public function testCreateDnsRecordRejectsTtlOutsideAllowedRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CreateDnsRecordRequest(type: 'A', value: '1.2.3.4', ttl: 100);
    }

    public function testCreateFtpAccountRoundTrip(): void
    {
        $data = ['name' => 'deploy', 'password' => 'secret1', 'dir' => 'web/'];
        $request = CreateFtpAccountRequest::fromArray($data);

        self::assertSame($data, $request->toArray());
    }

    public function testCreateFtpAccountRejectsShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CreateFtpAccountRequest(name: 'deploy', password: '123', dir: 'web/');
    }

    public function testCreateMailboxRoundTripWithQuota(): void
    {
        $data = ['local_part' => 'info', 'password' => 'secret1', 'quota' => 1024];
        $request = CreateMailboxRequest::fromArray($data);

        self::assertSame($data, $request->toArray());
    }

    public function testCreateMailboxFromArrayAcceptsDeprecatedEmailField(): void
    {
        $request = CreateMailboxRequest::fromArray(['email' => 'info', 'password' => 'secret1']);

        self::assertSame('info', $request->localPart);
        self::assertSame(['local_part' => 'info', 'password' => 'secret1'], $request->toArray());
    }

    public function testChangeDatabaseUserPasswordRejectsTooLongPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ChangeDatabaseUserPasswordRequest(str_repeat('a', 101));
    }

    public function testChangeDatabaseUserPasswordAcceptsValidPassword(): void
    {
        $request = new ChangeDatabaseUserPasswordRequest('newpassword');

        self::assertSame(['password' => 'newpassword'], $request->toArray());
    }

    public function testChangeFtpAccountPasswordRejectsShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ChangeFtpAccountPasswordRequest('123');
    }

    public function testUpdateMailboxRequiresPasswordOrQuota(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new UpdateMailboxRequest();
    }

    public function testUpdateMailboxRoundTripWithQuotaOnly(): void
    {
        $request = new UpdateMailboxRequest(quota: 2048);

        self::assertSame(['quota' => 2048], $request->toArray());
    }

    public function testSetDnssecRoundTrip(): void
    {
        $request = SetDnssecRequest::fromArray(['enabled' => true]);

        self::assertSame(['enabled' => true], $request->toArray());
    }

    public function testCreateBillingServiceRejectsInvalidPlatba(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CreateBillingServiceRequest(
            customer: 100,
            serviceId: 1,
            platba: 5,
            price: 49.0,
            text: 'Webhosting example.sk',
            date: '2026-12-01',
            splatnost: 14,
            type: 'proforma',
        );
    }

    public function testCreateBillingServiceRoundTripSerializesNumbersAsStrings(): void
    {
        $request = new CreateBillingServiceRequest(
            customer: 100,
            serviceId: 1,
            platba: 12,
            price: 49.0,
            text: 'Webhosting example.sk',
            date: '2026-12-01',
            splatnost: 14,
            type: 'proforma',
        );

        self::assertSame([
            'customer' => 100,
            'service_id' => 1,
            'platba' => 12,
            'price' => '49',
            'text' => 'Webhosting example.sk',
            'date' => '2026-12-01',
            'discount' => '0',
            'quantity' => '1',
            'splatnost' => '14',
            'type' => 'proforma',
            'domain_id' => null,
        ], $request->toArray());
    }

    public function testAssignSubuserDomainRequiresDomainOrDomainId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AssignSubuserDomainRequest();
    }

    public function testAssignSubuserDomainRejectsUnknownPrivilege(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AssignSubuserDomainRequest(domain: 'example.sk', privileges: ['bogus']);
    }

    public function testAssignSubuserDomainRoundTrip(): void
    {
        $request = new AssignSubuserDomainRequest(domain: 'example.sk', privileges: ['web', 'ftp']);

        self::assertSame(['domain' => 'example.sk', 'privileges' => ['web', 'ftp']], $request->toArray());
    }

    public function testRemoveSubuserDomainRequiresDomainOrDomainId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RemoveSubuserDomainRequest();
    }
}
