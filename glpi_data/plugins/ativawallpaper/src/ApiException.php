<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 400,
        public readonly string $errorCode = 'BAD_REQUEST',
        public readonly array $headers = []
    ) {
        parent::__construct($message);
    }
}
