<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Concern;

/**
 * Support for OpenAPI schemas declared with `additionalProperties: true`
 * (Invoice, InvoiceItem, BillingService columns pass through from the
 * billing database as-is and are not fully enumerated in the spec).
 */
trait HasExtraProperties
{
    /**
     * @param array<string, mixed> $data
     * @param string[] $knownKeys
     * @return array<string, mixed>
     */
    private static function pluckExtra(array $data, array $knownKeys): array
    {
        return array_diff_key($data, array_flip($knownKeys));
    }

    /**
     * @param array<string, mixed> $known
     * @return array<string, mixed>
     */
    private function mergeExtra(array $known): array
    {
        return array_merge($known, $this->extra);
    }
}
