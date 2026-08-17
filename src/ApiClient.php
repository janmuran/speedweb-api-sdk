<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk;

use JanMuran\SpeedwebApiSdk\Http\HttpClient;
use JanMuran\SpeedwebApiSdk\Resource\BillingResource;
use JanMuran\SpeedwebApiSdk\Resource\DatabasesResource;
use JanMuran\SpeedwebApiSdk\Resource\DnsResource;
use JanMuran\SpeedwebApiSdk\Resource\DomainsResource;
use JanMuran\SpeedwebApiSdk\Resource\FtpResource;
use JanMuran\SpeedwebApiSdk\Resource\MailboxesResource;
use JanMuran\SpeedwebApiSdk\Resource\SubusersResource;

/**
 * Entry point of the SDK. Composes one resource class per OpenAPI tag,
 * e.g. `$client->dns()->create($domainId, $request)`.
 */
final class ApiClient
{
    private readonly HttpClient $http;

    private ?BillingResource $billing = null;
    private ?DomainsResource $domains = null;
    private ?DnsResource $dns = null;
    private ?MailboxesResource $mailboxes = null;
    private ?FtpResource $ftp = null;
    private ?DatabasesResource $databases = null;
    private ?SubusersResource $subusers = null;

    public function __construct(
        private readonly ClientConfig $config,
        ?HttpClient $httpClient = null,
    ) {
        $this->http = $httpClient ?? new HttpClient($config);
    }

    /**
     * Convenience factory: `ApiClient::create($apiKey)` or with extra
     * {@see ClientConfig} options, e.g.
     * `ApiClient::create($apiKey, ['loggingEnabled' => true, 'logger' => $logger])`.
     *
     * @param array<string, mixed> $options
     */
    public static function create(string $apiKey, array $options = []): self
    {
        unset($options['apiKey']);

        return new self(new ClientConfig(...array_merge(['apiKey' => $apiKey], $options)));
    }

    public function getConfig(): ClientConfig
    {
        return $this->config;
    }

    public function billing(): BillingResource
    {
        return $this->billing ??= new BillingResource($this->http);
    }

    public function domains(): DomainsResource
    {
        return $this->domains ??= new DomainsResource($this->http);
    }

    public function dns(): DnsResource
    {
        return $this->dns ??= new DnsResource($this->http);
    }

    public function mailboxes(): MailboxesResource
    {
        return $this->mailboxes ??= new MailboxesResource($this->http);
    }

    public function ftp(): FtpResource
    {
        return $this->ftp ??= new FtpResource($this->http);
    }

    public function databases(): DatabasesResource
    {
        return $this->databases ??= new DatabasesResource($this->http);
    }

    public function subusers(): SubusersResource
    {
        return $this->subusers ??= new SubusersResource($this->http);
    }
}
