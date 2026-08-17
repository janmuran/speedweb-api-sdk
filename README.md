# speedweb-api-sdk

PHP SDK klient pre **Hosting Admin API** (Speedweb / Smartweb / Webcentrum / Inet / Mam / CMC Data / NS Hosting) —
generovaný podľa [`openapi.json`](openapi.json), OpenAPI 3.0 špecifikácie API.

Postavené na PSR-4, PSR-18 (HTTP client), PSR-17 (HTTP factories) a PSR-3 (logger), s Guzzle ako predvolenou
implementáciou HTTP klienta.

## Požiadavky

- PHP 8.2+
- Composer

## Inštalácia

Balík je publikovaný na Packagiste: [janmuran/speedweb-api-sdk](https://packagist.org/packages/janmuran/speedweb-api-sdk).

```bash
composer require janmuran/speedweb-api-sdk
```

Pre prácu priamo na tomto repozitári (vývoj SDK) použi namiesto toho:

```bash
composer install
```

## Rýchly štart

```php
use JanMuran\SpeedwebApiSdk\ApiClient;

$client = ApiClient::create('swk_xxxxxxxxxxxxxxxx');

foreach ($client->domains()->list() as $domain) {
    echo $domain->domain . "\n";
}

$invoice = $client->billing()->getInvoice(12345);
echo $invoice->total;

$record = $client->dns()->create(
    domainId: 1,
    request: new \JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest(
        type: 'A',
        value: '1.2.3.4',
        ttl: 3600,
        name: 'www',
    ),
);
```

### Voľba hostiteľa (brandu)

API beží pod viacerými doménami (`api.speedweb.sk`, `api.smartweb.eu`, `api.webcentrum.sk`, `api.inet.sk`,
`api.mam.sk`, `api.cmcdata.sk`, `api.nshosting.eu`). Predvolený je `api.speedweb.sk`:

```php
use JanMuran\SpeedwebApiSdk\ApiClient;
use JanMuran\SpeedwebApiSdk\ClientConfig;

$client = new ApiClient(ClientConfig::forHost('swk_xxxxxxxxxxxxxxxx', 'api.smartweb.eu'));
```

### Zapnutie logovania

Logovanie je defaultne vypnuté (`NullLogger`). Zapnete ho cez `ClientConfig` a dodáte vlastný PSR-3 logger
(napr. Monolog). Hlavička `Authorization` sa v logoch vždy maskuje.

```php
use JanMuran\SpeedwebApiSdk\ApiClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('speedweb-sdk');
$logger->pushHandler(new StreamHandler('php://stderr'));

$client = ApiClient::create('swk_xxxxxxxxxxxxxxxx', [
    'loggingEnabled' => true,
    'logger' => $logger,
]);
```

### Retry / timeouty

Sieťové chyby a nakonfigurované HTTP stavové kódy (`429, 500, 502, 503, 504` predvolene) sa automaticky
opakujú s exponenciálnym backoffom:

```php
$client = ApiClient::create('swk_xxxxxxxxxxxxxxxx', [
    'timeout' => 15.0,
    'maxRetries' => 3,
    'retryBaseDelayMs' => 200,
    'retryMultiplier' => 2.0,
    'retryMaxDelayMs' => 5000,
    'retryableStatusCodes' => [429, 500, 502, 503, 504],
]);
```

### Chybové stavy

Všetky výnimky dedia z `JanMuran\SpeedwebApiSdk\Exception\ApiException` (status kód, telo odpovede, pôvodný
request):

- `NetworkException` — request sa nepodarilo dokončiť ani po opakovaniach
- `AuthenticationException` — HTTP 401
- `ValidationException` — HTTP 422 (`getErrors()` vráti pole chýb)
- `RateLimitException` — HTTP 429 (`getRetryAfterSeconds()`)

```php
use JanMuran\SpeedwebApiSdk\Exception\ValidationException;

try {
    $client->mailboxes()->create($domainId, $request);
} catch (ValidationException $e) {
    foreach ($e->getErrors() as $field => $messages) {
        // ...
    }
}
```

## Architektúra

```
src/
  ApiClient.php          Vstupný bod, skladá Resource triedy: $client->dns(), $client->billing(), ...
  ClientConfig.php        Nemenná konfigurácia (API kľúč, base URI, timeouty, retry, logging)
  Http/HttpClient.php     PSR-18 wrapper: auth hlavička, JSON (de)serializácia, retry s backoffom, PSR-3 logging
  Exception/              ApiException a jeho podtriedy podľa HTTP stavu
  Model/                  DTO pre response schémy (fromArray()/toArray()), PaginatedCollection
  Model/Request/          DTO pre POST/PATCH telá požiadaviek (s klientskou validáciou)
  Resource/               Jedna trieda na tag z openapi.json (Billing, Domains, DNS, Mailboxes, FTP, Databases, Subusers)
```

## Testy

```bash
composer test
```

## Coding style

```bash
composer cs-check   # dry-run
composer cs-fix      # aplikuje opravy
```

Pozri aj [CHANGELOG.md](CHANGELOG.md) pre postup pri regenerácii SDK po zmene `openapi.json`.
