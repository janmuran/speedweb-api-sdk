<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateDnsRecordRequest;

final class DnsIntegrationTest extends IntegrationTestCase
{
    public function testListDnsRecords(): void
    {
        $domainId = $this->requireFirstDomainId();
        $records = $this->client->dns()->list($domainId);

        self::assertGreaterThanOrEqual(0, $records->total);
        fwrite(STDERR, sprintf("\n[dns] %d record(s) on domain #%d\n", $records->total, $domainId));
    }

    /**
     * Creates a TXT record (chosen because it can't break mail/web routing
     * the way an A/MX record could), updates it, then deletes it through
     * the DELETE endpoint — nothing is left behind on the domain.
     */
    public function testCreateUpdateAndDeleteDnsRecord(): void
    {
        $this->requireCreateOptIn();

        $domainId = $this->requireFirstDomainId();
        $suffix = self::uniqueSuffix();

        $created = $this->client->dns()->create($domainId, new CreateDnsRecordRequest(
            type: 'TXT',
            value: "speedweb-sdk-integration-test-{$suffix}",
            ttl: 300,
            name: '_sdk-test',
            note: 'Created by speedweb-api-sdk integration test suite',
        ));

        self::assertNotSame(0, $created->id);
        self::assertSame('TXT', $created->type);
        fwrite(STDERR, sprintf(
            "\n[dns] created record #%d on domain #%d (%s)\n",
            $created->id,
            $domainId,
            $created->name,
        ));

        $updated = $this->client->dns()->update($domainId, $created->id, new UpdateDnsRecordRequest(
            type: 'TXT',
            value: "speedweb-sdk-integration-test-{$suffix}-updated",
            ttl: 600,
            name: '_sdk-test',
            note: 'Updated by speedweb-api-sdk integration test suite',
        ));

        self::assertSame($created->id, $updated->id);
        self::assertSame(600, $updated->ttl);
        self::assertStringEndsWith('-updated', $updated->value);

        $this->client->dns()->delete($domainId, $created->id);
        fwrite(STDERR, sprintf("[dns] deleted record #%d — no leftovers\n", $created->id));
    }

    public function testDeleteNonexistentDnsRecordThrowsApiException(): void
    {
        $domainId = $this->requireFirstDomainId();

        $this->expectException(ApiException::class);
        $this->client->dns()->delete($domainId, 999999999);
    }
}
