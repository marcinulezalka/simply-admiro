<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Mail;

use Illuminate\Contracts\Config\Repository;

/**
 * Uzupełnia konfigurację frameworka o ustawienia poczty z Core, nie edytując plików szkieletu.
 *
 * Każdą wartość ustawia tylko wtedy, gdy szkielet (lub .env przez jego config) jej nie zdefiniował,
 * więc aplikacja zawsze może ją nadpisać.
 */
final readonly class MailLogDefaults
{
    private const string LOG_MAILER_CHANNEL = 'mail.mailers.log.channel';

    private const string SMTP_REQUIRE_TLS = 'mail.mailers.smtp.require_tls';

    private const string LOG_FILE = 'logs/email.log';

    private const string DEFAULT_CHANNEL = 'mail';

    /**
     * @param  string  $storagePath  katalog storage aplikacji (bez końcowego ukośnika)
     */
    public function __construct(
        private Repository $config,
        private string $storagePath,
    ) {}

    /**
     * Rejestruje dzienny kanał logów poczty, kieruje do niego mailer "log" i wymusza TLS dla SMTP.
     */
    public function apply(): void
    {
        $channel = $this->logChannel();

        if (! $this->config->has("logging.channels.{$channel}")) {
            $this->config->set("logging.channels.{$channel}", [
                'driver' => 'daily',
                'path' => $this->storagePath . '/' . self::LOG_FILE,
                'level' => 'debug',
                'days' => $this->config->get('mainboard.mail.log_days'),
                'replace_placeholders' => true,
            ]);
        }

        if ($this->config->get(self::LOG_MAILER_CHANNEL) === null) {
            $this->config->set(self::LOG_MAILER_CHANNEL, $channel);
        }

        if (! $this->config->has(self::SMTP_REQUIRE_TLS)) {
            $this->config->set(self::SMTP_REQUIRE_TLS, (bool) $this->config->get('mainboard.mail.require_tls'));
        }
    }

    /**
     * Nazwa kanału logów poczty: ustawiona dla mailera "log" albo domyślna z configu Core.
     */
    private function logChannel(): string
    {
        $channel = $this->config->get(self::LOG_MAILER_CHANNEL) ?? $this->config->get('mainboard.mail.log_channel');

        return is_string($channel) && $channel !== '' ? $channel : self::DEFAULT_CHANNEL;
    }
}
