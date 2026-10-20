<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Session\SessionManager;

/**
 * Przełącza config sesji na strefę (przed StartSession) i przywraca go po odpowiedzi.
 *
 * Bazowy config/session.php zapamiętuje raz, przy tworzeniu, więc kolejna strefa w tym samym
 * procesie (testy, Octane) zawsze startuje od wartości aplikacji, a nie od poprzedniej strefy.
 */
final readonly class ZoneSessionConfigurator
{
    /**
     * @param array<array-key, mixed> $baseSessionConfig
     * @param bool $forceSecure Ciasteczko tylko przez HTTPS (każde środowisko poza local).
     */
    public function __construct(
        private Repository $config,
        private SessionManager $session,
        private array $baseSessionConfig,
        private bool $forceSecure,
    ) {}

    /**
     * Ustawia sesję strefy; utworzone wcześniej sterowniki są porzucane, żeby nowa nazwa
     * ciasteczka, tabela i szyfrowanie zadziałały.
     */
    public function apply(ZoneDefinition $zone): void
    {
        $settings = array_replace($this->baseSessionConfig, $zone->session->toSessionConfig());

        if ($this->forceSecure) {
            $settings['secure'] = true;
        }

        $this->use($settings);
    }

    /**
     * Przywraca sesję aplikacji.
     */
    public function restore(): void
    {
        $this->use($this->baseSessionConfig);
    }

    /**
     * @param array<array-key, mixed> $settings
     */
    private function use(array $settings): void
    {
        $this->config->set('session', $settings);
        $this->session->forgetDrivers();
    }
}
