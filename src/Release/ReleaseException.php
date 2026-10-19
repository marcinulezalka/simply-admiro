<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Release;

use RuntimeException;

/**
 * Wyjątek oznaczający sytuację, w której wydanie nie może zostać wykonane.
 *
 * Komunikat jest przeznaczony dla użytkownika i trafia bezpośrednio na STDERR.
 */
final class ReleaseException extends RuntimeException {}
