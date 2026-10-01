<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;

/** Erro devolvido pelo servidor OAuth (codigo OAuth em $oauthError, ex.: invalid_grant). */
final class OAuthException extends RuntimeException
{
    public function __construct(string $message, public readonly string $oauthError = '')
    {
        parent::__construct($message);
    }
}
