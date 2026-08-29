<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class PaymentParentNotifier
{
    private string $smsApiUrl;
    private string $smsApiToken;
    private string $smsSender;
    private string $whatsappApiUrl;
    private string $whatsappApiToken;

    public function __construct(
        ?string $smsApiUrl = null,
        ?string $smsApiToken = null,
        ?string $smsSender = null,
        ?string $whatsappApiUrl = null,
        ?string $whatsappApiToken = null
    ) {
        $this->smsApiUrl = $smsApiUrl ?? $this->resolveConfigValue('PAYMENT_SMS_API_URL', 'SMS_API_URL');
        $this->smsApiToken = $smsApiToken ?? $this->resolveConfigValue('PAYMENT_SMS_API_TOKEN', 'SMS_API_TOKEN');
        $this->smsSender = $smsSender ?? $this->resolveConfigValue('PAYMENT_SMS_SENDER', 'SMS_FROM');
        $this->whatsappApiUrl = $whatsappApiUrl ?? $this->resolveConfigValue('PAYMENT_WHATSAPP_API_URL', 'WHATSAPP_API_URL');
        $this->whatsappApiToken = $whatsappApiToken ?? $this->resolveConfigValue('PAYMENT_WHATSAPP_API_TOKEN', 'WHATSAPP_API_TOKEN');
    }

    public function notifyAfterPayment(int $eleveId, int $fraisId, float $montant, string $libelle, string $devise = 'USD'): void
    {
        if ($eleveId <= 0) {
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.id, e.nom, e.postnom, e.prenom, p.telephone AS parent_phone, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN parents p ON p.id = e.parent_id '
            . 'WHERE e.id = :eleve_id LIMIT 1'
        );
        $stmt->execute([':eleve_id' => $eleveId]);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$eleve) {
            return;
        }

        $phone = $this->normalizePhone((string) ($eleve['parent_phone'] ?? ''));
        if ($phone === '') {
            return;
        }

        $studentName = trim(implode(' ', array_filter([
            $eleve['prenom'] ?? '',
            $eleve['nom'] ?? '',
            $eleve['postnom'] ?? '',
        ], static fn ($value) => trim((string) $value) !== '')));

        $paymentLabel = trim($libelle) !== '' ? $libelle : 'paiement scolaire';
        $currency = trim($devise) !== '' ? strtoupper($devise) : 'USD';
        $message = 'Bonjour, le paiement de ' . number_format($montant, 2, ',', ' ') . ' ' . $currency . ' pour ' . $paymentLabel
            . ' a bien été enregistré pour ' . $studentName . '.';

        if ($this->smsApiUrl !== '') {
            $this->sendSms($phone, $message);
        }

        if ($this->whatsappApiUrl !== '') {
            $this->sendWhatsapp($phone, $message);
        }
    }

    private function sendSms(string $phone, string $message): void
    {
        $payload = [
            'to' => $phone,
            'from' => $this->smsSender,
            'text' => $message,
        ];

        $this->sendJsonRequest($this->smsApiUrl, $payload, $this->smsApiToken);
    }

    private function sendWhatsapp(string $phone, string $message): void
    {
        $payload = [
            'to' => $phone,
            'message' => $message,
        ];

        $this->sendJsonRequest($this->whatsappApiUrl, $payload, $this->whatsappApiToken);
    }

    private function sendJsonRequest(string $url, array $payload, string $token = ''): void
    {
        if ($url === '') {
            return;
        }

        try {
            $json = json_encode($payload);
            if ($json === false) {
                return;
            }

            $headers = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ];
            if ($token !== '') {
                $headers[] = 'Authorization: Bearer ' . $token;
            }

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15,
                ]);
                curl_exec($ch);
                curl_close($ch);
                return;
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => $json,
                    'timeout' => 15,
                ],
            ]);

            @file_get_contents($url, false, $context);
        } catch (\Throwable $e) {
            error_log('PaymentParentNotifier::sendJsonRequest failed: ' . $e->getMessage());
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if ($phone === '') {
            return '';
        }

        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            return '+243' . substr($phone, 1);
        }

        return '+' . $phone;
    }

    private function resolveConfigValue(string ...$names): string
    {
        foreach ($names as $name) {
            $value = getenv($name);
            if ($value !== false && $value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }

            if (isset($_ENV[$name]) && trim((string) $_ENV[$name]) !== '') {
                return trim((string) $_ENV[$name]);
            }

            if (defined($name)) {
                $constantValue = constant($name);
                if (is_string($constantValue) && trim($constantValue) !== '') {
                    return trim($constantValue);
                }
            }
        }

        return '';
    }
}
