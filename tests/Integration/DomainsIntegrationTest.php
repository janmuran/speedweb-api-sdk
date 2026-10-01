<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Model\Request\SetDnssecRequest;

final class DomainsIntegrationTest extends IntegrationTestCase
{
    public function testListDomains(): void
    {
        $domains = $this->client->domains()->list();

        self::assertGreaterThanOrEqual(0, $domains->total);
        foreach ($domains as $domain) {
            self::assertGreaterThan(0, $domain->id);
            self::assertNotSame('', $domain->domain);
        }

        fwrite(STDERR, sprintf("\n[domains] %d domain(s) on account\n", $domains->total));
    }

    public function testGetDnssecStatus(): void
    {
        $domainId = $this->requireFirstDomainId();
        $dnssec = $this->client->domains()->getDnssec($domainId);

        self::assertIsBool($dnssec->enabled);
        fwrite(STDERR, sprintf(
            "\n[dnssec] domain #%d: enabled=%s status=%s\n",
            $domainId,
            $dnssec->enabled ? 'true' : 'false',
            $dnssec->status === null ? 'null' : (string) $dnssec->status,
        ));
    }

    /**
     * Reads the current DNSSEC status, flips it, then flips it back —
     * leaving the domain exactly as it was found. Skipped unless
     * SPEEDWEB_TEST_DESTRUCTIVE=1 since toggling DNSSEC queues real key
     * creation/removal on the domain.
     */
    public function testToggleDnssecRoundTrip(): void
    {
        $this->requireDestructiveOptIn();

        $domainId = $this->requireFirstDomainId();
        $original = $this->client->domains()->getDnssec($domainId);

        $flipped = $this->client->domains()->setDnssec($domainId, new SetDnssecRequest(enabled: !$original->enabled));
        self::assertSame(!$original->enabled, $flipped->enabled);

        $restored = $this->client->domains()->setDnssec($domainId, new SetDnssecRequest(enabled: $original->enabled));
        self::assertSame($original->enabled, $restored->enabled);

        fwrite(STDERR, sprintf(
            "\n[dnssec] domain #%d: toggled %s -> %s -> %s (restored)\n",
            $domainId,
            $original->enabled ? 'true' : 'false',
            $flipped->enabled ? 'true' : 'false',
            $restored->enabled ? 'true' : 'false',
        ));
    }
}
