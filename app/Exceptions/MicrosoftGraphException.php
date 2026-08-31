<?php

namespace App\Exceptions;

use RuntimeException;

class MicrosoftGraphException extends RuntimeException
{
    public function __construct(
        public readonly string $method,
        public readonly string $endpoint,
        public readonly int $status,
        public readonly string $graphCode,
        public readonly string $graphMessage,
    ) {
        parent::__construct(
            "Graph {$method} {$endpoint} returned {$status} [{$graphCode}]: {$graphMessage}"
        );
    }

    public function isNotFound(): bool
    {
        return $this->status === 404;
    }

    public function isDuplicateReference(): bool
    {
        return $this->status === 400
            && str_contains(strtolower($this->graphMessage), 'already exist');
    }
}
