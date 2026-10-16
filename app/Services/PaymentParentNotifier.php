<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class PaymentParentNotifier
{
    private string $provider;
    private string $channel;
    private string $smsApiUrl;
    private string $smsApiToken;
    private string $smsSender;
    private string $whatsappApiUrl;
    private string $whatsappApiToken;
    private string $infobipBaseUrl;
    private string $infobipApiKey;
    private string $infobipSmsFrom;
    private string $infobipWhatsappFrom;
    private string $twilioAccountSid;
    private string $twilioAuthToken;
    private string $twilioSmsFrom;
    private string $twilioWhatsappFrom;

    public function __construct(
        ?string $provider = null,
        ?string $smsApiUrl = null,
        ?string $smsApiToken = null,
        ?string $smsSender = null,
        ?string $whatsappApiUrl = null,
        ?string $whatsappApiToken = null,
        ?string $twilioAccountSid = null,
        ?string $twilioAuthToken = null,
        ?string $twilioSmsFrom = null,
        ?string $twilioWhatsappFrom = null
    ) {
        $this->provider = strtolower(trim((string) ($provider ?? $this->resolveConfigValue('PAYMENT_PROVIDER', 'PAYMENT_PROVIDER'))));
        if ($this->provider === '') {
            $this->provider = 'infobip';
        }

        $configuredChannel = $this->resolveConfigValue('PAYMENT_CHANNEL', 'PAYMENT_MESSAGE_CHANNEL', 'PAYMENT_CHANNELS');
        $this->channel = strtolower(trim($configuredChannel !== '' ? $configuredChannel : 'both'));
        if (!in_array($this->channel, ['sms', 'whatsapp', 'both'], true)) {
            $this->channel = 'both';
        }

        $this->smsApiUrl = $smsApiUrl ?? $this->resolveConfigValue('PAYMENT_SMS_API_URL', 'SMS_API_URL');
        $this->smsApiToken = $smsApiToken ?? $this->resolveConfigValue('PAYMENT_SMS_API_TOKEN', 'SMS_API_TOKEN');
        $this->smsSender = $smsSender ?? $this->resolveConfigValue('PAYMENT_SMS_SENDER', 'SMS_FROM');
        $this->whatsappApiUrl = $whatsappApiUrl ?? $this->resolveConfigValue('PAYMENT_WHATSAPP_API_URL', 'WHATSAPP_API_URL');
        $this->whatsappApiToken = $whatsappApiToken ?? $this->resolveConfigValue('PAYMENT_WHATSAPP_API_TOKEN', 'WHATSAPP_API_TOKEN');
        $this->infobipBaseUrl = $this->resolveConfigValue('PAYMENT_INFOBIP_BASE_URL', 'INFOBIP_BASE_URL');
        $this->infobipApiKey = $this->resolveConfigValue('PAYMENT_INFOBIP_API_KEY', 'INFOBIP_API_KEY');
        $this->infobipSmsFrom = $this->resolveConfigValue('PAYMENT_INFOBIP_SMS_FROM', 'INFOBIP_SMS_FROM');
        $this->infobipWhatsappFrom = $this->resolveConfigValue('PAYMENT_INFOBIP_WHATSAPP_FROM', 'INFOBIP_WHATSAPP_FROM');
        $this->twilioAccountSid = $twilioAccountSid ?? $this->resolveConfigValue('PAYMENT_TWILIO_ACCOUNT_SID', 'TWILIO_ACCOUNT_SID');
        $this->twilioAuthToken = $twilioAuthToken ?? $this->resolveConfigValue('PAYMENT_TWILIO_AUTH_TOKEN', 'TWILIO_AUTH_TOKEN');
        $this->twilioSmsFrom = $twilioSmsFrom ?? $this->resolveConfigValue('PAYMENT_TWILIO_SMS_FROM', 'TWILIO_SMS_FROM');
        $this->twilioWhatsappFrom = $twilioWhatsappFrom ?? $this->resolveConfigValue('PAYMENT_TWILIO_WHATSAPP_FROM', 'TWILIO_WHATSAPP_FROM');
    }

    public function notifyAfterPayment(int $eleveId, int $fraisId, float $montant, string $libelle, string $devise = 'USD'): array
    {
        if ($eleveId <= 0) {
            return ['sent' => false, 'status' => 'failed', 'reason' => 'invalid_student', 'message' => 'Envoi impossible : élève invalide.', 'details' => 'L’identifiant de l’élève est absent ou invalide.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT e.id, e.nom, e.postnom, e.prenom, ec.nom_etablissement AS school_name, '
            . 'p.telephone AS parent_phone, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e '
            . 'LEFT JOIN ecoles ec ON ec.id = e.ecole_id '
            . 'LEFT JOIN parents p ON p.id = e.parent_id '
            . 'WHERE e.id = :eleve_id LIMIT 1'
        );
        $stmt->execute([':eleve_id' => $eleveId]);
        $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$eleve) {
            return ['sent' => false, 'status' => 'failed', 'reason' => 'student_not_found', 'message' => 'Envoi impossible : élève introuvable.', 'details' => 'Aucun élève ne correspond à l’identifiant fourni.'];
        }

        $phone = $this->normalizePhone((string) ($eleve['parent_phone'] ?? ''));
        if ($phone === '') {
            return ['sent' => false, 'status' => 'failed', 'reason' => 'missing_parent_phone', 'message' => 'Envoi impossible : aucun numéro de téléphone parent n’a été trouvé.', 'details' => 'Le parent associé à l’élève n’a pas de téléphone valide ou le champ est vide.'];
        }

        $studentName = trim(implode(' ', array_filter([
            $eleve['nom'] ?? '',
            $eleve['postnom'] ?? '',
            $eleve['prenom'] ?? '',
        ], static fn ($value) => trim((string) $value) !== '')));

        $paymentLabel = trim($libelle) !== '' ? $libelle : 'paiement scolaire';
        $currency = trim($devise) !== '' ? strtoupper($devise) : 'USD';
        $schoolName = trim((string) ($eleve['school_name'] ?? ''));
        if ($schoolName === '') {
            $schoolName = 'Établissement scolaire';
        }
        $parentName = trim((string) ($eleve['parent_nom_responsable'] ?? ''));
        $greeting = $parentName !== '' ? 'Bonjour ' . $parentName . ',' : 'Bonjour,';
        $message = '[' . $schoolName . "]\n" . $greeting . ' le paiement de ' . number_format($montant, 2, ',', ' ') . ' ' . $currency
            . ' pour ' . $paymentLabel . ' a bien été enregistré pour l\'élève ' . $studentName . '.';

        return $this->dispatchMessage($phone, $message, $schoolName);
    }

    public function notifyAfterRegistration(int $eleveId): array
    {
        if ($eleveId <= 0) {
            return ['sent' => false, 'delivered' => false, 'status' => 'failed', 'reason' => 'invalid_student', 'message' => 'Notification impossible : élève invalide.', 'details' => 'L’identifiant de l’élève est absent ou invalide.'];
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT e.id, e.nom, e.postnom, e.prenom, e.matricule, ec.nom_etablissement AS school_name, '
            . 'p.telephone AS parent_phone, p.nom_responsable AS parent_nom_responsable '
            . 'FROM eleves e LEFT JOIN ecoles ec ON ec.id = e.ecole_id '
            . 'LEFT JOIN parents p ON p.id = e.parent_id WHERE e.id = :eleve_id LIMIT 1'
        );
        $stmt->execute([':eleve_id' => $eleveId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            return ['sent' => false, 'delivered' => false, 'status' => 'failed', 'reason' => 'student_not_found', 'message' => 'Notification impossible : élève introuvable.', 'details' => 'Aucun élève ne correspond à cet identifiant.'];
        }

        $schoolName = trim((string) ($student['school_name'] ?? '')) ?: 'Établissement scolaire';
        $studentName = trim(implode(' ', array_filter(
            [$student['nom'] ?? '', $student['postnom'] ?? '', $student['prenom'] ?? ''],
            static fn ($value): bool => trim((string) $value) !== ''
        )));
        $parentName = trim((string) ($student['parent_nom_responsable'] ?? ''));
        $greeting = $parentName !== '' ? 'Bonjour ' . $parentName . ',' : 'Bonjour,';
        $matricule = trim((string) ($student['matricule'] ?? ''));
        $message = '[' . $schoolName . "]\n" . $greeting . ' l’inscription de ' . $studentName
            . ($matricule !== '' ? ' (matricule ' . $matricule . ')' : '')
            . ' a été validée. Bienvenue dans notre établissement.';

        $phone = $this->normalizePhone((string) ($student['parent_phone'] ?? ''));
        if ($phone === '') {
            return ['sent' => false, 'delivered' => false, 'status' => 'failed', 'reason' => 'missing_parent_phone', 'message' => 'Notification d’inscription non envoyée.', 'details' => 'Le parent associé à cet élève n’a pas de numéro de téléphone valide.'];
        }

        return $this->dispatchMessage($phone, $message, $schoolName);
    }

    public function sendSchoolAnnouncement(int $ecoleId, string $title, string $body): array
    {
        if ($ecoleId <= 0) {
            return ['sent' => false, 'status' => 'failed', 'reason' => 'invalid_school', 'message' => 'Envoi impossible : école invalide.', 'details' => 'Aucune école valide n’a été sélectionnée.'];
        }

        $db = Database::getConnection();
        $schoolStmt = $db->prepare('SELECT nom_etablissement FROM ecoles WHERE id = :id LIMIT 1');
        $schoolStmt->execute([':id' => $ecoleId]);
        $schoolName = trim((string) $schoolStmt->fetchColumn());
        if ($schoolName === '') {
            return ['sent' => false, 'status' => 'failed', 'reason' => 'school_not_found', 'message' => 'Envoi impossible : école introuvable.', 'details' => 'Aucun établissement ne correspond à cette école.'];
        }

        $recipientStmt = $db->prepare(
            'SELECT DISTINCT p.id, p.telephone FROM parents p '
            . 'INNER JOIN eleves e ON e.parent_id = p.id '
            . 'LEFT JOIN inscriptions i ON i.eleve_id = e.id '
            . 'LEFT JOIN classes c ON c.id = i.classe_id '
            . 'WHERE p.telephone IS NOT NULL AND TRIM(p.telephone) <> \'\' '
            . 'AND (p.ecole_id = :parent_school OR e.ecole_id = :student_school OR c.ecole_id = :class_school)'
        );
        $recipientStmt->execute([
            ':parent_school' => $ecoleId,
            ':student_school' => $ecoleId,
            ':class_school' => $ecoleId,
        ]);

        $phones = [];
        foreach ($recipientStmt->fetchAll(PDO::FETCH_ASSOC) as $recipient) {
            $phone = $this->normalizePhone((string) ($recipient['telephone'] ?? ''));
            if ($phone !== '') {
                $phones[$phone] = $phone;
            }
        }

        if ($phones === []) {
            return [
                'sent' => false,
                'status' => 'failed',
                'reason' => 'no_parent_phone',
                'message' => 'Aucun communiqué n’a été envoyé.',
                'details' => 'Aucun numéro de parent valide n’a été trouvé pour cette école.',
                'recipients_total' => 0,
                'recipients_delivered' => 0,
                'recipients_failed' => 0,
            ];
        }

        $message = '[' . $schoolName . "]\nCOMMUNIQUÉ : " . trim($title) . "\n" . trim($body);
        $delivered = 0;
        $fullySent = 0;
        $failureReasons = [];
        foreach ($phones as $phone) {
            $result = $this->dispatchMessage($phone, $message, $schoolName);
            if (!empty($result['delivered'])) {
                $delivered++;
            }
            if (!empty($result['sent'])) {
                $fullySent++;
            }
            if (empty($result['sent'])) {
                $failureReasons[] = $result['details'] ?? $result['reason'] ?? 'Échec du fournisseur.';
            }
        }

        $status = $fullySent === count($phones) ? 'success' : ($delivered > 0 ? 'partial' : 'failed');
        return [
            'sent' => $status === 'success',
            'status' => $status,
            'reason' => $status === 'success' ? '' : ($status === 'partial' ? 'partial_delivery' : 'all_deliveries_failed'),
            'message' => $status === 'success'
                ? 'Communiqué envoyé à tous les parents concernés.'
                : ($status === 'partial' ? 'Communiqué envoyé partiellement; certains envois ont échoué.' : 'Le communiqué n’a pu être envoyé à aucun parent.'),
            'details' => $fullySent . ' envoi(s) complet(s) sur ' . count($phones) . '; '
                . $delivered . ' parent(s) ont reçu au moins un canal.'
                . ($failureReasons !== [] ? ' Détail du premier échec : ' . $failureReasons[0] : ''),
            'recipients_total' => count($phones),
            'recipients_delivered' => $delivered,
            'recipients_failed' => count($phones) - $delivered,
        ];
    }

    private function dispatchMessage(string $phone, string $message, string $schoolName): array
    {
        $attempts = [];
        if ($this->provider === 'infobip') {
            if ($this->shouldSendSms()) {
                $attempts[] = $this->sendInfobipSms($phone, $message, $schoolName);
            }
            if ($this->shouldSendWhatsapp()) {
                $attempts[] = $this->sendInfobipWhatsapp($phone, $message);
            }
        } elseif ($this->provider === 'twilio') {
            if ($this->shouldSendSms()) {
                $attempts[] = $this->sendTwilioSms($phone, $message);
            }
            if ($this->shouldSendWhatsapp()) {
                $attempts[] = $this->sendTwilioWhatsapp($phone, $message);
            }
        } else {
            if ($this->shouldSendSms()) {
                $attempts[] = $this->sendSms($phone, $message, $schoolName);
            }
            if ($this->shouldSendWhatsapp()) {
                $attempts[] = $this->sendWhatsapp($phone, $message);
            }
        }

        if ($attempts === []) {
            return ['sent' => false, 'delivered' => false, 'status' => 'failed', 'reason' => 'no_channel_configured', 'message' => 'Aucun canal de notification n’est configuré.', 'details' => 'Le fournisseur ou le canal WhatsApp/SMS n’est pas prêt pour cet envoi.'];
        }

        $successes = array_filter($attempts, static fn ($attempt) => !empty($attempt['sent']));
        $attemptDetails = array_map(static function (array $attempt): string {
            $channel = ($attempt['channel'] ?? '') === 'sms' ? 'SMS' : 'WhatsApp';
            $result = !empty($attempt['sent']) ? 'envoyé' : 'échec';
            $details = trim((string) ($attempt['details'] ?? ''));
            return $channel . ' : ' . $result . ($details !== '' ? ' — ' . $details : '');
        }, $attempts);
        $attemptChannels = array_values(array_filter(array_map(
            static fn ($attempt) => $attempt['channel'] ?? null,
            $attempts
        )));
        $channelLabels = array_map(
            static fn (string $channel): string => $channel === 'sms' ? 'SMS' : 'WhatsApp',
            $attemptChannels
        );

        if (count($successes) === count($attempts)) {
            return [
                'sent' => true,
                'delivered' => true,
                'status' => 'success',
                'reason' => '',
                'message' => 'Notification envoyée par ' . implode(' et ', $channelLabels) . '.',
                'details' => implode("\n", $attemptDetails),
                'channel' => implode(',', $attemptChannels),
                'attempts' => $attempts,
            ];
        }

        $status = $successes ? 'partial' : 'failed';
        return [
            'sent' => false,
            'delivered' => $successes !== [],
            'status' => $status,
            'reason' => $status === 'partial' ? 'partial_delivery' : 'all_channels_failed',
            'message' => $status === 'partial'
                ? 'Notification partiellement envoyée. Un canal a échoué.'
                : 'Échec de l’envoi sur tous les canaux.',
            'details' => implode("\n", $attemptDetails),
            'channel' => implode(',', $attemptChannels),
            'attempts' => $attempts,
        ];
    }

    private function shouldSendSms(): bool
    {
        return $this->channel === 'sms' || $this->channel === 'both';
    }

    private function shouldSendWhatsapp(): bool
    {
        return $this->channel === 'whatsapp' || $this->channel === 'both';
    }

    private function sendSms(string $phone, string $message, string $schoolName): array
    {
        $payload = [
            'to' => $phone,
            'from' => $this->resolveSchoolSmsSender($schoolName, $this->smsSender),
            'text' => $message,
        ];

        return $this->sendJsonRequest($this->smsApiUrl, $payload, $this->smsApiToken, 'sms');
    }

    private function sendWhatsapp(string $phone, string $message): array
    {
        $payload = [
            'to' => $phone,
            'message' => $message,
        ];

        return $this->sendJsonRequest($this->whatsappApiUrl, $payload, $this->whatsappApiToken, 'whatsapp');
    }

    private function sendInfobipSms(string $phone, string $message, string $schoolName): array
    {
        $apiKey = trim($this->infobipApiKey);
        $baseUrl = trim($this->infobipBaseUrl);
        $from = trim($this->infobipSmsFrom);

        if ($apiKey === '' || $baseUrl === '') {
            return ['sent' => false, 'channel' => 'sms', 'reason' => 'missing_infobip_config', 'message' => 'Infobip SMS non configuré.', 'details' => 'La clé API Infobip ou l’URL de base du compte est absente.'];
        }

        $payload = [
            'messages' => [[
                'destinations' => [[
                    'to' => ltrim($phone, '+'),
                ]],
                'sender' => $this->resolveSchoolSmsSender($schoolName, $from),
                'content' => [
                    'text' => $message,
                ],
            ]],
        ];

        return $this->sendJsonRequest($baseUrl . '/sms/3/messages', $payload, $apiKey, 'sms', true);
    }

    private function sendInfobipWhatsapp(string $phone, string $message): array
    {
        $apiKey = trim($this->infobipApiKey);
        $baseUrl = trim($this->infobipBaseUrl);
        $from = trim($this->infobipWhatsappFrom);

        if ($apiKey === '' || $baseUrl === '') {
            return ['sent' => false, 'channel' => 'whatsapp', 'reason' => 'missing_infobip_config', 'message' => 'Infobip WhatsApp non configuré.', 'details' => 'La clé API Infobip ou l’URL de base du compte est absente.'];
        }
        if ($from === '') {
            return ['sent' => false, 'channel' => 'whatsapp', 'reason' => 'missing_infobip_whatsapp_sender', 'message' => 'Expéditeur WhatsApp Infobip non configuré.', 'details' => 'Renseignez le numéro WhatsApp approuvé et associé à votre compte Infobip.'];
        }

        $payload = [
            'from' => $from,
            'to' => ltrim($phone, '+'),
            'content' => [
                'text' => $message,
            ],
        ];

        return $this->sendJsonRequest($baseUrl . '/whatsapp/1/message/text', $payload, $apiKey, 'whatsapp', true);
    }

    private function resolveSchoolSmsSender(string $schoolName, string $fallback): string
    {
        $sender = function_exists('iconv')
            ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $schoolName)
            : $schoolName;
        $sender = preg_replace('/[^A-Za-z0-9]/', '', $sender !== false ? $sender : $schoolName);
        $sender = substr($sender ?? '', 0, 11);

        return $sender !== '' ? $sender : (trim($fallback) !== '' ? trim($fallback) : 'InfoSMS');
    }

    private function sendTwilioSms(string $phone, string $message): array
    {
        $accountSid = trim($this->twilioAccountSid);
        $authToken = trim($this->twilioAuthToken);
        $from = trim($this->twilioSmsFrom);

        if ($accountSid === '' || $authToken === '' || $from === '') {
            return ['sent' => false, 'channel' => 'sms', 'reason' => 'missing_twilio_config', 'message' => 'Twilio SMS non configuré.', 'details' => 'Les identifiants Twilio SMS sont absents.'];
        }

        $payload = [
            'To' => $phone,
            'From' => $from,
            'Body' => $message,
        ];

        return $this->sendFormRequest('https://api.twilio.com/2010-04-01/Accounts/' . $accountSid . '/Messages.json', $payload, $accountSid, $authToken, 'sms');
    }

    private function sendTwilioWhatsapp(string $phone, string $message): array
    {
        $accountSid = trim($this->twilioAccountSid);
        $authToken = trim($this->twilioAuthToken);
        $from = trim($this->twilioWhatsappFrom);

        if ($accountSid === '' || $authToken === '' || $from === '') {
            return ['sent' => false, 'channel' => 'whatsapp', 'reason' => 'missing_twilio_config', 'message' => 'Twilio WhatsApp non configuré.', 'details' => 'Les identifiants Twilio WhatsApp sont absents.'];
        }

        $payload = [
            'To' => 'whatsapp:' . ltrim($phone, '+'),
            'From' => $from,
            'Body' => $message,
        ];

        return $this->sendFormRequest('https://api.twilio.com/2010-04-01/Accounts/' . $accountSid . '/Messages.json', $payload, $accountSid, $authToken, 'whatsapp');
    }

    private function sendJsonRequest(string $url, array $payload, string $token = '', string $channel = 'sms', bool $infobipMode = false): array
    {
        if ($url === '') {
            return ['sent' => false, 'channel' => $channel, 'reason' => 'missing_url', 'message' => 'URL d’envoi absente.', 'details' => 'L’URL du fournisseur est vide.'];
        }

        try {
            $json = json_encode($payload);
            if ($json === false) {
                return ['sent' => false, 'channel' => $channel, 'reason' => 'json_encode_failed', 'message' => 'Payload invalide.', 'details' => 'Impossible de sérialiser le message JSON pour l’envoi.'];
            }

            $headers = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
                'Accept: application/json',
                'User-Agent: KC_SMA/1.0',
            ];
            if ($token !== '') {
                if ($infobipMode) {
                    $headers[] = 'Authorization: App ' . $token;
                    $headers[] = 'x-api-key: ' . $token;
                } else {
                    $headers[] = 'Authorization: Bearer ' . $token;
                }
            }

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_FAILONERROR => false,
                ]);
                $response = curl_exec($ch);
                $errorNo = curl_errno($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = $errorNo !== 0 ? curl_error($ch) : null;
                curl_close($ch);

                if ($response === false || $errorNo !== 0 || ($statusCode >= 400 && $statusCode !== 401 && $statusCode !== 402)) {
                    $providerError = is_string($response) ? $this->parseProviderResponse($response, $infobipMode, $channel) : null;
                    $details = $error ?: ($providerError['details'] ?? 'Erreur HTTP ' . $statusCode);
                    $reason = $errorNo === CURLE_OPERATION_TIMEDOUT ? 'provider_timeout' : 'provider_http_error';
                    if ($reason === 'provider_timeout') {
                        $details = 'Délai d’attente dépassé lors de la connexion au fournisseur de notification. Vérifiez l’accès réseau et la configuration du service.';
                    }
                    return ['sent' => false, 'channel' => $channel, 'reason' => $reason, 'message' => 'Échec de l’envoi de message au parent.', 'details' => $details];
                }

                $parsed = $this->parseProviderResponse($response, $infobipMode, $channel);
                if ($parsed !== null) {
                    return $parsed;
                }

                return ['sent' => true, 'channel' => $channel, 'reason' => '', 'message' => 'Message envoyé.', 'details' => 'La notification a bien été envoyée avec succès.'];
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => $json,
                    'timeout' => 15,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return ['sent' => false, 'channel' => $channel, 'reason' => 'stream_context_failed', 'message' => 'Échec de l’envoi de message au parent.', 'details' => 'Le contexte HTTP local n’a pas pu envoyer la requête.'];
            }

            $parsed = $this->parseProviderResponse($response, $infobipMode, $channel);
            if ($parsed !== null) {
                return $parsed;
            }

            return ['sent' => true, 'channel' => $channel, 'reason' => '', 'message' => 'Message envoyé.', 'details' => 'La notification a bien été envoyée avec succès.'];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            error_log('PaymentParentNotifier::sendJsonRequest failed: ' . $message);
            return ['sent' => false, 'channel' => $channel, 'reason' => 'exception', 'message' => 'Échec de l’envoi de message au parent.', 'details' => $message];
        }
    }

    private function sendFormRequest(string $url, array $payload, string $username, string $password, string $channel = 'sms'): array
    {
        try {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_USERPWD => $username . ':' . $password,
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                ]);
                $response = curl_exec($ch);
                $errorNo = curl_errno($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = $errorNo !== 0 ? curl_error($ch) : null;
                curl_close($ch);

                if ($response === false || $errorNo !== 0 || ($statusCode >= 400 && $statusCode !== 401 && $statusCode !== 402)) {
                    $details = $error ?: 'Erreur HTTP ' . $statusCode;
                    $reason = $errorNo === CURLE_OPERATION_TIMEDOUT ? 'provider_timeout' : 'provider_http_error';
                    if ($reason === 'provider_timeout') {
                        $details = 'Délai d’attente dépassé lors de la connexion au fournisseur de notification. Vérifiez l’accès réseau et la configuration du service.';
                    }
                    return ['sent' => false, 'channel' => $channel, 'reason' => $reason, 'message' => 'Échec de l’envoi de message au parent.', 'details' => $details];
                }

                $parsed = $this->parseProviderResponse($response, false, $channel);
                if ($parsed !== null) {
                    return $parsed;
                }

                return ['sent' => true, 'channel' => $channel, 'reason' => '', 'message' => 'Message envoyé.', 'details' => 'La notification a bien été envoyée avec succès.'];
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded\r\n' . 'Authorization: Basic ' . base64_encode($username . ':' . $password),
                    'content' => http_build_query($payload),
                    'timeout' => 15,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return ['sent' => false, 'channel' => $channel, 'reason' => 'stream_context_failed', 'message' => 'Échec de l’envoi de message au parent.', 'details' => 'Le contexte HTTP local n’a pas pu envoyer la requête.'];
            }

            return ['sent' => true, 'channel' => $channel, 'reason' => '', 'message' => 'Message envoyé.', 'details' => 'La notification a bien été envoyée avec succès.'];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            error_log('PaymentParentNotifier::sendFormRequest failed: ' . $message);
            return ['sent' => false, 'channel' => $channel, 'reason' => 'exception', 'message' => 'Échec de l’envoi de message au parent.', 'details' => $message];
        }
    }

    private function parseProviderResponse(string $response, bool $infobipMode, string $channel): ?array
    {
        if (trim($response) === '') {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        if ($infobipMode) {
            $requestError = $decoded['requestError'] ?? null;
            if (is_array($requestError)) {
                $text = $requestError['serviceException']['text'] ?? $requestError['text'] ?? 'Erreur Infobip.';
                return ['sent' => false, 'channel' => $channel, 'reason' => 'infobip_api_error', 'message' => 'Échec de l’envoi de message au parent.', 'details' => (string) $text];
            }

            $messages = $decoded['messages'] ?? [];
            if (is_array($messages) && !empty($messages)) {
                $status = $messages[0]['status'] ?? null;
                if (is_array($status)) {
                    $groupId = $status['groupId'] ?? null;
                    $groupName = $status['groupName'] ?? null;
                    $description = $status['description'] ?? null;
                    if ($groupId !== null && (int) $groupId !== 1) {
                        return ['sent' => false, 'channel' => $channel, 'reason' => 'infobip_send_failed', 'message' => 'Échec de l’envoi de message au parent.', 'details' => trim((string) ($description ?? $groupName ?? 'La plateforme Infobip a refusé l’envoi.'))];
                    }
                    if ($groupId !== null && (int) $groupId === 1) {
                        return ['sent' => true, 'channel' => $channel, 'reason' => '', 'message' => 'Message envoyé.', 'details' => 'La notification a bien été envoyée avec succès.'];
                    }
                }
            }
        }

        if (isset($decoded['status']) && is_array($decoded['status'])) {
            $statusCode = $decoded['status']['code'] ?? null;
            $statusMessage = $decoded['status']['message'] ?? null;
            if ($statusCode !== null && (int) $statusCode >= 400) {
                return ['sent' => false, 'channel' => $channel, 'reason' => 'provider_api_error', 'message' => 'Échec de l’envoi de message au parent.', 'details' => trim((string) ($statusMessage ?? 'Le fournisseur a refusé l’envoi.'))];
            }
        }

        if (isset($decoded['error'])) {
            $errorText = $decoded['error']['message'] ?? $decoded['error'] ?? 'Erreur fournisseur.';
            return ['sent' => false, 'channel' => $channel, 'reason' => 'provider_api_error', 'message' => 'Échec de l’envoi de message au parent.', 'details' => trim((string) $errorText)];
        }

        if (isset($decoded['success']) && $decoded['success'] === false) {
            $reason = $decoded['message'] ?? $decoded['error'] ?? 'Le fournisseur a refusé l’envoi.';
            return ['sent' => false, 'channel' => $channel, 'reason' => 'provider_api_error', 'message' => 'Échec de l’envoi de message au parent.', 'details' => trim((string) $reason)];
        }

        return null;
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
