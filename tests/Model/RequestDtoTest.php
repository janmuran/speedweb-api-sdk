<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;
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
        $data = ['email' => 'info', 'password' => 'secret1', 'quota' => 1024];
        $request = CreateMailboxRequest::fromArray($data);

        self::assertSame($data, $request->toArray());
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
}
