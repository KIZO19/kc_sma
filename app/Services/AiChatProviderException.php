<?php

namespace App\Services;

use RuntimeException;

class AiChatProviderException extends RuntimeException
{
    public const QUOTA = 'quota';
    public const RATE_LIMIT = 'rate_limit';
    public const AUTHENTICATION = 'authentication';
    public const MODEL = 'model';
    public const UNAVAILABLE = 'unavailable';
    public const OTHER = 'other';

    public function __construct(
        private string $category,
        string $message
    ) {
        parent::__construct($message);
    }

    public function category(): string
    {
        return $this->category;
    }
}
