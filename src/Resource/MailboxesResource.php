<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Mailbox;
use JanMuran\SpeedwebApiSdk\Model\MailboxWithSize;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateMailboxRequest;

/**
 * `Mailboxes` tag: domain email accounts.
 */
final class MailboxesResource extends AbstractResource
{
    /**
     * @return Mailbox[]
     */
    public function list(int $domainId): array
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/mailboxes");

        return array_map(
            static fn (array $item): Mailbox => Mailbox::fromArray($item),
            $data['data'] ?? [],
        );
    }

    /**
     * @return MailboxWithSize[]
     */
    public function listWithSizes(int $domainId): array
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/mailboxes/sizes");

        return array_map(
            static fn (array $item): MailboxWithSize => MailboxWithSize::fromArray($item),
            $data['data'] ?? [],
        );
    }

    public function create(int $domainId, CreateMailboxRequest $request): Mailbox
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/domains/{$domainId}/mailboxes",
            body: $request->toArray(),
        );

        return Mailbox::fromArray($data['data'] ?? []);
    }
}
