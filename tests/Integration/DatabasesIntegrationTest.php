<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Model\Database;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;

final class DatabasesIntegrationTest extends IntegrationTestCase
{
    public function testListDatabases(): void
    {
        $domainId = $this->requireFirstDomainId();
        $databases = $this->client->databases()->list($domainId);

        self::assertGreaterThanOrEqual(0, $databases->total);
        fwrite(STDERR, sprintf("\n[databases] %d database(s) on domain #%d\n", $databases->total, $domainId));
    }

    public function testListDatabaseUsers(): void
    {
        $database = $this->requireFirstDatabase();

        $users = $this->client->databases()->listUsers($database->domainId, $database->id);

        self::assertGreaterThanOrEqual(0, $users->total);
        fwrite(STDERR, sprintf("\n[databases] %d user(s) on database #%d\n", $users->total, $database->id));
    }

    /**
     * Mutates the password of an *existing* database user (there's no way
     * to create a throwaway one through this API), so it's gated behind its
     * own opt-in separate from SPEEDWEB_TEST_CREATE. The new password is
     * printed so it can be recovered/reset afterwards.
     */
    public function testChangeDatabaseUserPassword(): void
    {
        $this->requireDestructiveOptIn();

        $database = $this->requireFirstDatabase();
        $users = $this->client->databases()->listUsers($database->domainId, $database->id);
        if ($users->items === []) {
            self::markTestSkipped("Database #{$database->id} has no users to change the password of.");
        }

        $user = $users->items[0];
        $newPassword = self::randomPassword();

        $this->client->databases()->changeUserPassword(
            $database->domainId,
            $database->id,
            $user->id,
            new ChangeDatabaseUserPasswordRequest($newPassword),
        );

        fwrite(STDERR, sprintf(
            "\n[databases] changed password of user #%d ('%s') on database #%d to: %s\n",
            $user->id,
            $user->user,
            $database->id,
            $newPassword,
        ));

        self::assertTrue(true);
    }

    private function requireFirstDatabase(): Database
    {
        $domainId = $this->requireFirstDomainId();
        $databases = $this->client->databases()->list($domainId);

        if ($databases->items === []) {
            self::markTestSkipped("Domain #{$domainId} has no databases to run database tests against.");
        }

        return $databases->items[0];
    }
}
