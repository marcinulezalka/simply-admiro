<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Http\Middleware;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Symfony\Component\HttpFoundation\Response;

/**
 * Nagłówki bezpieczeństwa każdej strefy: ramki, MIME, referrer, funkcje przeglądarki, COOP,
 * HSTS (tylko HTTPS) i CSP z nonce przekazanym do Vite. Strefy z logowaniem dostają dodatkowo
 * zakaz cache'owania i X-Robots-Tag.
 */
final readonly class SecurityHeaders
{
    private const string NONCE_PLACEHOLDER = '{nonce}';

    /**
     * @var array<string, string>
     */
    private const array HEADERS = [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'same-origin',
        'Cross-Origin-Opener-Policy' => 'same-origin',
    ];

    /**
     * @var array<string, string>
     */
    private const array PRIVATE_HEADERS = [
        'Cache-Control' => 'no-store, private',
        'Pragma' => 'no-cache',
    ];

    public function __construct(
        private CurrentZone $zone,
        private Repository $config,
        private Vite $vite,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = $this->cspEnabled() ? $this->vite->useCspNonce() : null;
        $isPrivate = $this->zone->has() && $this->zone->get()->requiresLogin();

        $response = $next($request);
        $headers = $response->headers;

        foreach (self::HEADERS as $name => $value) {
            $headers->set($name, $value);
        }

        $headers->set('Permissions-Policy', $this->config->string('mainboard.security.permissions_policy'));

        if ($request->isSecure() && $this->config->boolean('mainboard.security.hsts.enabled')) {
            $headers->set('Strict-Transport-Security', 'max-age=' . $this->config->integer('mainboard.security.hsts.max_age') . '; includeSubDomains');
        }

        if ($nonce !== null) {
            $headers->set('Content-Security-Policy', str_replace(self::NONCE_PLACEHOLDER, $nonce, $this->config->string('mainboard.security.csp.policy')));
        }

        if ($isPrivate) {
            foreach (self::PRIVATE_HEADERS as $name => $value) {
                $headers->set($name, $value);
            }

            $headers->set('X-Robots-Tag', $this->config->string('mainboard.zones.robots'));
        }

        return $response;
    }

    /**
     * CSP jest pomijana przy serwerze deweloperskim Vite (skrypty spoza 'self').
     */
    private function cspEnabled(): bool
    {
        return $this->config->boolean('mainboard.security.csp.enabled') && ! $this->vite->isRunningHot();
    }
}
