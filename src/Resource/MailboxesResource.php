<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Mailbox;
use JanMuran\SpeedwebApiSdk\Model\MailboxWithSize;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateMailboxRequest;

/**
 * `Mailboxes` tag: domain email accounts.
 */
final class MailboxesResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<Mailbox>
     */
    public function list(int $domainId, ?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/mailboxes", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, Mailbox::class);
    }

    /**
     * @return PaginatedCollection<MailboxWithSize>
     */
    public function listWithSizes(int $domainId, ?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/mailboxes/sizes", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, MailboxWithSize::class);
    }

    public function create(int $domainId, CreateMailboxRequest $request, ?string $idempotencyKey = null): Mailbox
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/domains/{$domainId}/mailboxes",
            body: $request->toArray(),
            headers: $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [],
        );

        return Mailbox::fromArray($data['data'] ?? []);
    }

    public function update(int $domainId, int $mailboxId, UpdateMailboxRequest $request): MailboxWithSize
    {
        $data = $this->http->request(
            'PATCH',
            "/api/v1/domains/{$domainId}/mailboxes/{$mailboxId}",
            body: $request->toArray(),
        );

        return MailboxWithSize::fromArray($data['data'] ?? []);
    }

    public function delete(int $domainId, int $mailboxId): void
    {
        $this->http->request('DELETE', "/api/v1/domains/{$domainId}/mailboxes/{$mailboxId}");
    }
}
