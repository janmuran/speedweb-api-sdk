<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Model\Request\AssignSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\RemoveSubuserDomainRequest;

final class SubusersIntegrationTest extends IntegrationTestCase
{
    public function testListSubusers(): void
    {
        $subusers = $this->client->subusers()->list();

        self::assertIsArray($subusers);
        foreach ($subusers as $subuser) {
            self::assertGreaterThan(0, $subuser->id);
            self::assertNotSame('', $subuser->login);
        }

        fwrite(STDERR, sprintf("\n[subusers] %d subuser(s) on account\n", count($subusers)));
    }

    public function testListDomainsForFirstSubuser(): void
    {
        $subusers = $this->client->subusers()->list();
        if ($subusers === []) {
            self::markTestSkipped('No subusers on this account to list domains for.');
        }

        $domains = $this->client->subusers()->listDomains($subusers[0]->id);

        self::assertIsArray($domains);
        fwrite(STDERR, sprintf(
            "\n[subusers] subuser #%d has %d assigned domain(s)\n",
            $subusers[0]->id,
            count($domains),
        ));
    }

    /**
     * Assigns a real domain to a real subuser, then immediately removes it
     * again — leaving the subuser's assignments as they were found (unless
     * the domain was already assigned, in which case this is a skipped
     * no-op to avoid clobbering an existing assignment's privileges).
     */
    public function testAssignAndUnassignDomainRoundTrip(): void
    {
        $this->requireDestructiveOptIn();

        $subusers = $this->client->subusers()->list();
        if ($subusers === []) {
            self::markTestSkipped('No subusers on this account to assign a domain to.');
        }
        $subuserId = $subusers[0]->id;

        $domainId = $this->requireFirstDomainId();
        $existingAssignments = $this->client->subusers()->listDomains($subuserId);
        foreach ($existingAssignments as $assignment) {
            if ($assignment->domainId === $domainId) {
                self::markTestSkipped("Domain #{$domainId} is already assigned to subuser #{$subuserId}; skipping to avoid clobbering it.");
            }
        }

        $assigned = $this->client->subusers()->assignDomain($subuserId, new AssignSubuserDomainRequest(domainId: $domainId));
        self::assertSame($domainId, $assigned->domainId);
        fwrite(STDERR, sprintf("\n[subusers] assigned domain #%d to subuser #%d\n", $domainId, $subuserId));

        $this->client->subusers()->unassignDomain($subuserId, new RemoveSubuserDomainRequest(domainId: $domainId));
        fwrite(STDERR, sprintf("[subusers] unassigned domain #%d from subuser #%d — no leftovers\n", $domainId, $subuserId));
    }
}
