<?php

namespace App\Exceptions;

use RuntimeException;

class DhlApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?int $httpStatus = null,
        private readonly ?string $requestId = null,
        private readonly array $details = [],
    ) {
        parent::__construct($message, $httpStatus ?? 0);
    }

    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    public function details(): array
    {
        return $this->details;
    }
}
