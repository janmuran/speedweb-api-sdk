<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeFtpAccountPasswordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;

final class FtpIntegrationTest extends IntegrationTestCase
{
    public function testListFtpAccounts(): void
    {
        $domainId = $this->requireFirstDomainId();
        $accounts = $this->client->ftp()->list($domainId);

        self::assertGreaterThanOrEqual(0, $accounts->total);
        fwrite(STDERR, sprintf("\n[ftp] %d account(s) on domain #%d\n", $accounts->total, $domainId));
    }

    /**
     * Creates a throwaway FTP account, changes its password, then deletes
     * it again through the DELETE endpoint — nothing is left behind on the
     * domain.
     */
    public function testCreateChangePasswordAndDeleteFtpAccount(): void
    {
        $this->requireCreateOptIn();

        $domainId = $this->requireFirstDomainId();
        $suffix = strtolower(substr(str_replace('-', '', self::uniqueSuffix()), 0, 12));
        $password = self::randomPassword();

        $created = $this->client->ftp()->create($domainId, new CreateFtpAccountRequest(
            name: "sdktest{$suffix}",
            password: $password,
            dir: '/sdk-test',
        ));

        // The API appends "@domain" to the account name automatically
        // (documented in openapi.json), so the response name won't match
        // the requested name verbatim.
        self::assertNotSame(0, $created->id);
        self::assertStringStartsWith("sdktest{$suffix}", $created->name);
        self::assertSame($created->name, $created->username);
        fwrite(STDERR, sprintf(
            "\n[ftp] created account #%d '%s' on domain #%d\n",
            $created->id,
            $created->name,
            $domainId,
        ));

        $this->client->ftp()->changePassword($domainId, $created->id, new ChangeFtpAccountPasswordRequest(self::randomPassword()));
        fwrite(STDERR, sprintf("[ftp] changed password of account #%d\n", $created->id));

        $this->client->ftp()->delete($domainId, $created->id);
        fwrite(STDERR, sprintf("[ftp] deleted account #%d — no leftovers\n", $created->id));
    }

    public function testDeleteNonexistentFtpAccountThrowsApiException(): void
    {
        $domainId = $this->requireFirstDomainId();

        $this->expectException(ApiException::class);
        $this->client->ftp()->delete($domainId, 999999999);
    }
}
