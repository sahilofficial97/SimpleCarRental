<?php

namespace App\Service\Payment;

class SentooClient
{
    public function __construct(
        private $httpClient,
        private readonly string $baseUrl,
        private readonly string $merchantId,
        private readonly string $secret,
        private readonly string $currency,
        private readonly string $defaultReturnUrl,
    ) {
    }

    /**
     * Create a payment and return an array with url and raw response.
     * @return array{url: string|null, raw: array<string,mixed>|string}
     */
    public function createPayment(
        int $amountMinorUnits,
        string $description,
        \DateTimeInterface $expiresAt,
        ?string $returnPayload = null,
        ?string $overrideReturnUrl = null,
    ): array {
        $returnUrl = $overrideReturnUrl ?: $this->defaultReturnUrl;
        if ($returnPayload) {
            $sep = str_contains($returnUrl, '?') ? '&' : '?';
            $returnUrl .= $sep . 'return=' . rawurlencode($returnPayload);
        }

        $endpoint = rtrim($this->baseUrl, '/') . '/v1/payment/new';

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'X-SENTOO-SECRET' => $this->secret,
        ];

        $body = [
            'sentoo_merchant' => $this->merchantId,
            'sentoo_currency' => $this->currency,
            'sentoo_amount' => (string) $amountMinorUnits,
            'sentoo_description' => $description,
            'sentoo_expires' => $expiresAt->format(DATE_ATOM),
            'sentoo_return_url' => $returnUrl,
        ];

        $response = $this->httpClient->request('POST', $endpoint, [
            'headers' => $headers,
            'body' => $body,
        ]);

        $contentType = $response->getHeaders()['content-type'][0] ?? '';
        $raw = null;
        try {
            $raw = str_contains($contentType, 'application/json')
                ? $response->toArray(false)
                : $response->getContent(false);
        } catch (\Throwable) {
            $raw = $response->getContent(false);
        }

        $url = $raw['success']['data']['url'] ?? null;

        return ['url' => $url, 'raw' => $raw ?? ''];
    }
}
