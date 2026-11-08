<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\AiChatProviderException;
use App\Services\AiChatService;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class AssistantController
{
    public function reply(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            $this->respond(['error' => 'Méthode non autorisée.'], 405);
            return;
        }

        Auth::requireAuth();
        $user = Auth::user();
        if ($user === null) {
            $this->respond(['error' => 'Authentification requise.'], 401);
            return;
        }

        if (!AI_CHAT_ENABLED) {
            $this->respond(['error' => 'L’assistant IA est temporairement désactivé.'], 503);
            return;
        }

        $csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (
            $csrfToken === ''
            || !hash_equals((string) ($_SESSION['ai_chat_csrf'] ?? ''), $csrfToken)
        ) {
            $this->respond(['error' => 'La session a expiré. Rechargez la page puis réessayez.'], 403);
            return;
        }

        if (!AiChatService::isConfigured()) {
            $this->respond([
                'error' => 'L’assistant IA n’est pas encore configuré. Contactez l’administrateur de la plateforme.',
            ], 503);
            return;
        }

        $requestBody = (string) file_get_contents('php://input');
        if (strlen($requestBody) > 65536) {
            $this->respond(['error' => 'La conversation est trop volumineuse.'], 413);
            return;
        }

        try {
            $payload = json_decode($requestBody, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->respond(['error' => 'La requête reçue est invalide.'], 400);
            return;
        }

        try {
            $conversation = $this->validateConversation($payload);
        } catch (InvalidArgumentException $exception) {
            $this->respond(['error' => $exception->getMessage()], 400);
            return;
        }

        if (!$this->allowRequest()) {
            $this->respond(['error' => 'Vous envoyez des messages trop rapidement. Réessayez dans un instant.'], 429);
            return;
        }

        try {
            $this->respond(['answer' => AiChatService::reply($conversation)]);
        } catch (AiChatProviderException $exception) {
            $statusCode = $exception->category() === AiChatProviderException::RATE_LIMIT ? 429 : 503;
            error_log('AssistantController::reply provider failure: ' . $exception->category());
            $this->respond(['error' => $exception->getMessage()], $statusCode);
        } catch (RuntimeException | JsonException $exception) {
            error_log('AssistantController::reply failed: ' . $exception->getMessage());
            $this->respond([
                'error' => 'L’assistant ne peut pas répondre pour le moment. Veuillez réessayer plus tard.',
            ], 502);
        }
    }

    private function validateConversation($payload): array
    {
        if (!is_array($payload) || !isset($payload['messages']) || !is_array($payload['messages'])) {
            throw new InvalidArgumentException('Le message envoyé est invalide.');
        }

        $messages = $payload['messages'];
        if (count($messages) < 1) {
            throw new InvalidArgumentException('La conversation doit contenir au moins un message.');
        }
        if (count($messages) > 20) {
            throw new InvalidArgumentException('La conversation est trop longue. Démarrez une nouvelle conversation.');
        }

        $conversation = [];
        foreach (array_slice($messages, -12) as $message) {
            if (
                !is_array($message)
                || !in_array($message['role'] ?? null, ['user', 'assistant'], true)
                || !is_string($message['content'] ?? null)
            ) {
                throw new InvalidArgumentException('Le message envoyé est invalide.');
            }

            $content = trim($message['content']);
            if ($content === '' || strlen($content) > 8000) {
                throw new InvalidArgumentException('Chaque message doit contenir au maximum 2 000 caractères.');
            }

            $conversation[] = ['role' => $message['role'], 'content' => $content];
        }

        if (($conversation[count($conversation) - 1]['role'] ?? null) !== 'user') {
            throw new InvalidArgumentException('Envoyez une question pour continuer la conversation.');
        }

        return $conversation;
    }

    private function allowRequest(): bool
    {
        $now = time();
        $recentRequests = array_values(array_filter(
            $_SESSION['ai_chat_requests'] ?? [],
            static fn ($timestamp): bool => is_int($timestamp) && $timestamp > $now - 60
        ));

        if (count($recentRequests) >= 15 || ($recentRequests !== [] && end($recentRequests) > $now - 2)) {
            $_SESSION['ai_chat_requests'] = $recentRequests;
            return false;
        }

        $recentRequests[] = $now;
        $_SESSION['ai_chat_requests'] = $recentRequests;
        return true;
    }

    private function respond(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
