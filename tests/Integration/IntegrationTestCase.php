<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\ApiClient;
use PHPUnit\Framework\TestCase;

/**
 * Base class for tests that hit a real Hosting Admin API instance instead of
 * a mock. Skips itself whenever SPEEDWEB_API_KEY / SPEEDWEB_BASE_URI aren't
 * configured, so `composer test` stays fully offline for everyone who hasn't
 * opted in. See tests/Integration/.env.testing.dist for how to opt in.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected ApiClient $client;

    private static bool $dotEnvLoaded = false;

    protected function setUp(): void
    {
        self::loadDotEnvOnce();

        $apiKey = self::env('SPEEDWEB_API_KEY');
        $baseUri = self::env('SPEEDWEB_BASE_URI');

        if ($apiKey === null || $baseUri === null) {
            self::markTestSkipped(
                'Set SPEEDWEB_API_KEY and SPEEDWEB_BASE_URI (env vars, or copy '
                . 'tests/Integration/.env.testing.dist to .env.testing in the repo root) '
                . 'to run integration tests against a real API.',
            );
        }

        $this->client = ApiClient::create($apiKey, [
            'baseUri' => rtrim($baseUri, '/'),
            'timeout' => 20.0,
        ]);
    }

    /**
     * Opt-in gate for tests that create a real resource on the account.
     * The API now has DELETE endpoints for DNS records / FTP accounts /
     * mailboxes, so these tests clean up after themselves — but they still
     * touch the real account in between, hence the opt-in. Requires
     * SPEEDWEB_TEST_CREATE=1.
     */
    protected function requireCreateOptIn(): void
    {
        if (!self::flag('SPEEDWEB_TEST_CREATE')) {
            self::markTestSkipped(
                'Set SPEEDWEB_TEST_CREATE=1 to run tests that create (and then delete) a DNS '
                . 'record / FTP account / mailbox on the account.',
            );
        }
    }

    /**
     * Opt-in gate for tests that mutate an *existing* resource on the
     * account (changing a real database user's password, toggling DNSSEC,
     * assigning/unassigning a domain on a real subuser). Requires
     * SPEEDWEB_TEST_DESTRUCTIVE=1.
     */
    protected function requireDestructiveOptIn(): void
    {
        if (!self::flag('SPEEDWEB_TEST_DESTRUCTIVE')) {
            self::markTestSkipped(
                'Set SPEEDWEB_TEST_DESTRUCTIVE=1 to run tests that mutate an existing resource '
                . 'on the account (database user password, DNSSEC, subuser domain assignment).',
            );
        }
    }

    /**
     * Opt-in gate for tests that create/update/delete a real billing
     * service (topay). Separate from {@see requireCreateOptIn()} because
     * billed services can have real financial consequences (recurring
     * invoices) on the account. Requires SPEEDWEB_TEST_BILLING_WRITE=1.
     */
    protected function requireBillingWriteOptIn(): void
    {
        if (!self::flag('SPEEDWEB_TEST_BILLING_WRITE')) {
            self::markTestSkipped(
                'Set SPEEDWEB_TEST_BILLING_WRITE=1 to run tests that create/update/delete a '
                . 'real billed service (topay) on the account. This can have real financial '
                . 'consequences (recurring invoices), so it is opt-in separately from '
                . 'SPEEDWEB_TEST_CREATE.',
            );
        }
    }

    /**
     * First domain to run domain-scoped tests against. Override with
     * SPEEDWEB_TEST_DOMAIN_ID to pin a specific domain instead of whichever
     * one happens to be first in the list.
     */
    protected function requireFirstDomainId(): int
    {
        $override = self::env('SPEEDWEB_TEST_DOMAIN_ID');
        if ($override !== null) {
            return (int) $override;
        }

        $domains = $this->client->domains()->list();
        if ($domains->items === []) {
            self::markTestSkipped('No domains on this account to run domain-scoped tests against.');
        }

        return $domains->items[0]->id;
    }

    /**
     * Customer id to use for billing service write tests. Override with
     * SPEEDWEB_TEST_BILLING_CUSTOMER_ID to pin a known-valid one; falls back
     * to $default (e.g. copied from an existing service) when unset.
     */
    protected function billingCustomerId(int $default): int
    {
        $override = self::env('SPEEDWEB_TEST_BILLING_CUSTOMER_ID');

        return $override !== null ? (int) $override : $default;
    }

    protected static function uniqueSuffix(): string
    {
        return date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }

    protected static function randomPassword(): string
    {
        return 'Sdk-' . bin2hex(random_bytes(9));
    }

    private static function env(string $name): ?string
    {
        $value = getenv($name);

        return $value === false || trim($value) === '' ? null : trim($value);
    }

    private static function flag(string $name): bool
    {
        $value = self::env($name);

        return $value !== null && in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private static function loadDotEnvOnce(): void
    {
        if (self::$dotEnvLoaded) {
            return;
        }

        self::$dotEnvLoaded = true;

        $path = dirname(__DIR__, 2) . '/.env.testing';
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim(trim($value), "\"'");

            if ($key !== '' && getenv($key) === false) {
                putenv("{$key}={$value}");
            }
        }
    }
}
