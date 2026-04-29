<?php
/**
 * Exceção lançada pelo AuthService em situações de falha de autenticação.
 *
 * Usar uma exceção dedicada (em vez de RuntimeException genérica) permite
 * que o AuthController capture apenas erros de auth sem engolir outros bugs.
 */
declare(strict_types=1);

namespace FetecPy\Exceptions;

use RuntimeException;

class AuthException extends RuntimeException
{
    // Códigos semânticos para facilitar o tratamento no controller
    // sem depender de strings "mágicas" comparadas com ===

    /** PIN incorreto para usuário existente */
    public const PIN_INVALIDO = 1;

    /** Muitas tentativas erradas — aguardar antes de tentar de novo */
    public const RATE_LIMIT   = 2;

    /** Token ausente, expirado ou inválido */
    public const TOKEN_INVALIDO = 3;
}
