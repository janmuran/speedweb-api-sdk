<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateMailboxRequest;

final class MailboxesIntegrationTest extends IntegrationTestCase
{
    public function testListMailboxes(): void
    {
        $domainId = $this->requireFirstDomainId();
        $mailboxes = $this->client->mailboxes()->list($domainId);

        self::assertGreaterThanOrEqual(0, $mailboxes->total);
        fwrite(STDERR, sprintf("\n[mailboxes] %d mailbox(es) on domain #%d\n", $mailboxes->total, $domainId));
    }

    public function testListMailboxesWithSizes(): void
    {
        $domainId = $this->requireFirstDomainId();
        $mailboxes = $this->client->mailboxes()->listWithSizes($domainId);

        self::assertGreaterThanOrEqual(0, $mailboxes->total);
        foreach ($mailboxes as $mailbox) {
            self::assertNotSame('', $mailbox->name);
        }
    }

    /**
     * Creates a throwaway mailbox, updates its quota, then deletes it again
     * through the DELETE endpoint — nothing is left behind on the domain.
     */
    public function testCreateUpdateAndDeleteMailbox(): void
    {
        $this->requireCreateOptIn();

        $domainId = $this->requireFirstDomainId();
        $domain = $this->findDomain($domainId);
        $localPart = 'sdktest' . substr(strtolower(str_replace('-', '', self::uniqueSuffix())), -6);
        $password = self::randomPassword();

        $created = $this->client->mailboxes()->create($domainId, new CreateMailboxRequest(
            localPart: $localPart,
            password: $password,
            quota: 100,
        ));

        self::assertNotSame(0, $created->id);
        fwrite(STDERR, sprintf(
            "\n[mailboxes] created mailbox #%d '%s@%s' on domain #%d\n",
            $created->id,
            $localPart,
            $domain,
            $domainId,
        ));

        $updated = $this->client->mailboxes()->update($domainId, $created->id, new UpdateMailboxRequest(quota: 200));
        self::assertSame(200, $updated->quota);
        fwrite(STDERR, sprintf("[mailboxes] updated quota of mailbox #%d to 200 MB\n", $created->id));

        $this->client->mailboxes()->delete($domainId, $created->id);
        fwrite(STDERR, sprintf("[mailboxes] deleted mailbox #%d — no leftovers\n", $created->id));
    }

    public function testDeleteNonexistentMailboxThrowsApiException(): void
    {
        $domainId = $this->requireFirstDomainId();

        $this->expectException(ApiException::class);
        $this->client->mailboxes()->delete($domainId, 999999999);
    }

    private function findDomain(int $domainId): string
    {
        foreach ($this->client->domains()->list() as $domain) {
            if ($domain->id === $domainId) {
                return $domain->domain;
            }
        }

        self::markTestSkipped("Domain #{$domainId} no longer present in the domain list.");
    }
}
