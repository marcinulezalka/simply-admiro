<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Security;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Middleware\TrustProxies;

/**
 * Przekazuje mainboard.security.trusted_proxies do globalnego middleware TrustProxies,
 * żeby IP klienta (limity, logi) pochodziło z X-Forwarded-For tylko od zaufanego proxy.
 */
final readonly class TrustedProxies
{
    private const string ALL = '*';

    public function __construct(
        private Repository $config,
    ) {}

    public function apply(): void
    {
        $proxies = $this->config->get('mainboard.security.trusted_proxies');

        if (! is_string($proxies) || trim($proxies) === '') {
            return;
        }

        TrustProxies::at(trim($proxies) === self::ALL ? self::ALL : array_values(array_filter(array_map(trim(...), explode(',', $proxies)))));
    }
}
