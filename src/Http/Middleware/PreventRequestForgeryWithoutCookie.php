<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

/**
 * Ochrona CSRF bez ciasteczka `XSRF-TOKEN`: ma ono jedną nazwę dla całej domeny, więc strefy
 * z osobnymi sesjami nadpisywałyby sobie tokeny. Formularze wysyłają `_token`, skrypty czytają
 * `<meta name="csrf-token">`.
 */
final class PreventRequestForgeryWithoutCookie extends PreventRequestForgery
{
    /**
     * @var bool
     */
    protected $addHttpCookie = false;
}
