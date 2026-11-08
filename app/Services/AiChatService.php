<?php

namespace App\Services;

use RuntimeException;

class AiChatService
{
    public static function isConfigured(): bool
    {
        return AI_CHAT_ENABLED
            && AI_CHAT_ENDPOINT !== ''
            && AI_CHAT_API_KEY !== ''
            && !str_starts_with(AI_CHAT_API_KEY, 'replace-with-')
            && AI_CHAT_MODEL !== '';
    }

    public static function reply(array $conversation): string
    {
        if (!self::isConfigured()) {
            throw new RuntimeException('AI chat provider is not configured.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for AI chat.');
        }

        $messages = [[
            'role' => 'system',
            'content' => 'Tu es l’assistant de KC_SMA, une application de gestion scolaire. '
                . 'Réponds en français, clairement et avec bienveillance. Aide sur l’utilisation générale '
                . 'de l’application et les questions générales. Tu n’as accès à aucune donnée de compte '
                . 'ou d’école : ne prétends jamais en consulter. Si tu ignores une information propre à '
                . 'KC_SMA, dis-le et oriente l’utilisateur vers l’administrateur de son école. '
                . 'Ne demande pas de mots de passe, de données personnelles ou confidentielles.',
        ]];
        foreach ($conversation as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        $requestBody = json_encode([
            'model' => AI_CHAT_MODEL,
            'messages' => $messages,
            'temperature' => 0.5,
            'max_tokens' => 800,
        ], JSON_THROW_ON_ERROR);

        $handle = curl_init(AI_CHAT_ENDPOINT);
        if ($handle === false) {
            throw new RuntimeException('Unable to initialize the AI chat request.');
        }

        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if (AI_CHAT_API_KEY !== '') {
            $headers[] = 'Authorization: Bearer ' . AI_CHAT_API_KEY;
        }

        $configured = curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if (!$configured) {
            curl_close($handle);
            throw new RuntimeException('Unable to configure the AI chat request.');
        }

        $responseBody = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($handle);
        curl_close($handle);

        if (!is_string($responseBody)) {
            throw new RuntimeException('AI chat provider request failed: ' . $curlError);
        }
        if ($statusCode < 200 || $statusCode >= 300) {
            $providerResponse = json_decode($responseBody, true);
            $providerCode = is_array($providerResponse)
                ? (string) ($providerResponse['error']['code'] ?? '')
                : '';
            $providerType = is_array($providerResponse)
                ? (string) ($providerResponse['error']['type'] ?? '')
                : '';
            $errorCategory = strtolower($providerCode . ' ' . $providerType);

            if ($statusCode === 429 && (
                strpos($errorCategory, 'quota') !== false
                || strpos($errorCategory, 'billing') !== false
            )) {
                $category = AiChatProviderException::QUOTA;
            } elseif ($statusCode === 429) {
                $category = AiChatProviderException::RATE_LIMIT;
            } elseif (in_array($statusCode, [401, 403], true)) {
                $category = AiChatProviderException::AUTHENTICATION;
            } elseif ($statusCode === 404) {
                $category = AiChatProviderException::MODEL;
            } elseif ($statusCode >= 500) {
                $category = AiChatProviderException::UNAVAILABLE;
            } else {
                $category = AiChatProviderException::OTHER;
            }

            error_log('AI chat provider returned HTTP ' . $statusCode . ' (' . $category . ').');
            $messages = [
                AiChatProviderException::QUOTA => 'Le compte OpenAI n’a plus de quota ou de crédits disponibles. Vérifiez sa facturation et ses limites de dépenses.',
                AiChatProviderException::RATE_LIMIT => 'OpenAI a atteint une limite de requêtes ou de quota. Vérifiez les crédits, la facturation et les limites API, puis réessayez.',
                AiChatProviderException::AUTHENTICATION => 'La clé OpenAI a été refusée. Vérifiez que AI_CHAT_API_KEY contient une clé API active pour le bon projet.',
                AiChatProviderException::MODEL => 'Le modèle OpenAI configuré est introuvable ou inaccessible. Vérifiez AI_CHAT_MODEL et les accès de votre projet.',
                AiChatProviderException::UNAVAILABLE => 'Le service OpenAI est temporairement indisponible. Veuillez réessayer plus tard.',
                AiChatProviderException::OTHER => 'OpenAI a refusé la requête. Vérifiez la configuration du fournisseur puis réessayez.',
            ];
            throw new AiChatProviderException($category, $messages[$category]);
        }

        $response = json_decode($responseBody, true, 32, JSON_THROW_ON_ERROR);
        $answer = $response['choices'][0]['message']['content'] ?? null;
        if (!is_string($answer) || trim($answer) === '') {
            error_log('AI chat provider response did not contain a text answer.');
            throw new RuntimeException('AI chat provider response did not contain an answer.');
        }

        return trim($answer);
    }
}
