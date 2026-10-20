<?php

namespace App\Services;

class InfobipPeopleSync
{
    public static function syncParent(array $parent): array
    {
        $provider = self::configValue('PAYMENT_PROVIDER', 'infobip');
        if (strtolower($provider) !== 'infobip') {
            return ['synced' => false, 'skipped' => true, 'reason' => 'provider_not_infobip'];
        }

        $baseUrl = rtrim(self::configValue('PAYMENT_INFOBIP_BASE_URL'), '/');
        $apiKey = self::configValue('PAYMENT_INFOBIP_API_KEY');
        if ($baseUrl === '' || $apiKey === '') {
            return ['synced' => false, 'reason' => 'missing_infobip_config'];
        }

        $parentId = (int) ($parent['id'] ?? 0);
        $name = trim((string) ($parent['nom_responsable'] ?? ''));
        $phone = self::normalizePhone((string) ($parent['telephone'] ?? ''));
        if ($parentId <= 0 || $name === '' || $phone === '') {
            return ['synced' => false, 'reason' => 'invalid_parent_contact'];
        }

        $payload = json_encode([
            'persons' => [[
                'externalId' => (string) $parentId,
                'firstName' => $name,
                'contactInformation' => [[
                    'identifier' => $phone,
                    'type' => 'PHONE',
                ]],
            ]],
        ]);
        if ($payload === false) {
            return ['synced' => false, 'reason' => 'json_encode_failed'];
        }

        $url = $baseUrl . '/people/3/persons/upsert';
        $response = self::sendRequest($url, $apiKey, $payload);
        if (!$response['ok']) {
            return ['synced' => false, 'reason' => $response['reason']];
        }

        $decoded = json_decode($response['body'], true);
        $result = is_array($decoded) ? ($decoded['results'][0] ?? null) : null;
        if (!is_array($result) || !in_array($result['status'] ?? '', ['CREATED', 'UPDATED', 'NOT_MODIFIED'], true)) {
            $reason = is_array($decoded) && !empty($decoded['errors'][0]['message'])
                ? (string) $decoded['errors'][0]['message']
                : 'Infobip did not confirm the contact upsert.';
            return ['synced' => false, 'reason' => $reason];
        }

        return [
            'synced' => true,
            'status' => (string) $result['status'],
            'person_id' => $result['id'] ?? null,
        ];
    }

    private static function sendRequest(string $url, string $apiKey, string $payload): array
    {
        if (function_exists('curl_init')) {
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Authorization: App ' . $apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15,
            ]);

            $body = curl_exec($handle);
            $errorNo = curl_errno($handle);
            $error = $errorNo !== 0 ? curl_error($handle) : '';
            $statusCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            if (!is_string($body) || $errorNo !== 0) {
                return ['ok' => false, 'reason' => $error !== '' ? $error : 'Infobip request failed.'];
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'PATCH',
                    'header' => implode("\r\n", [
                        'Authorization: App ' . $apiKey,
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ]),
                    'content' => $payload,
                    'timeout' => 15,
                    'ignore_errors' => true,
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (!is_string($body)) {
                $error = error_get_last();
                return ['ok' => false, 'reason' => $error['message'] ?? 'Infobip request failed.'];
            }
            $statusLine = $http_response_header[0] ?? '';
            $statusCode = preg_match('/\s(\d{3})(?:\s|$)/', $statusLine, $matches)
                ? (int) $matches[1]
                : 0;
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $decoded = json_decode($body, true);
            $providerMessage = is_array($decoded)
                ? (string) ($decoded['requestError']['serviceException']['text'] ?? $decoded['message'] ?? '')
                : '';
            return [
                'ok' => false,
                'reason' => $providerMessage !== '' ? $providerMessage : 'Infobip HTTP ' . $statusCode,
            ];
        }

        return ['ok' => true, 'body' => $body];
    }

    private static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone)) ?? '';
        if (str_starts_with($phone, '00')) {
            $phone = substr($phone, 2);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        } elseif (str_starts_with($phone, '0')) {
            $phone = '243' . substr($phone, 1);
        }

        return $phone;
    }

    private static function configValue(string $name, string $default = ''): string
    {
        $value = getenv($name);
        if ($value !== false && trim((string) $value) !== '') {
            return trim((string) $value);
        }

        if (isset($_ENV[$name]) && trim((string) $_ENV[$name]) !== '') {
            return trim((string) $_ENV[$name]);
        }

        if (defined($name)) {
            $constant = constant($name);
            if (is_string($constant) && trim($constant) !== '') {
                return trim($constant);
            }
        }

        return $default;
    }
}
