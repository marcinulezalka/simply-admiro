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

namespace Simplysmart\Mainboard\Listeners;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Log\LogManager;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Address;

/**
 * Odnotowuje w kanale logów poczty każdą wiadomość wysłaną prawdziwym transportem (SMTP itp.).
 *
 * Transport "log" sam zapisuje całą wiadomość w tym kanale, więc poczta wysłana przez niego
 * jest pomijana, żeby nie logować tej samej wiadomości dwa razy.
 */
final readonly class LogSentMail
{
    private const string LOG_TRANSPORT = 'log';

    public function __construct(
        private LogManager $logManager,
        private Repository $config,
    ) {}

    public function handle(MessageSent $event): void
    {
        $mailer = $event->data['mailer'] ?? null;
        $mailer = is_string($mailer) ? $mailer : $this->stringConfig('mail.default');

        if ($this->config->get("mail.mailers.{$mailer}.transport") === self::LOG_TRANSPORT) {
            return;
        }

        $message = $event->message;

        $this->logManager->channel($this->stringConfig('mail.mailers.log.channel'))->info('Mail sent', [
            'mailer' => $mailer,
            'message_id' => $event->sent->getMessageId(),
            'from' => $this->formatAddresses($message->getFrom()),
            'to' => $this->formatAddresses($message->getTo()),
            'cc' => $this->formatAddresses($message->getCc()),
            'bcc' => $this->formatAddresses($message->getBcc()),
            'subject' => $message->getSubject(),
        ]);
    }

    private function stringConfig(string $key): string
    {
        $value = $this->config->get($key);

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<Address>  $addresses
     * @return list<string>
     */
    private function formatAddresses(array $addresses): array
    {
        return array_values(array_map(
            static fn(Address $address): string => $address->toString(),
            $addresses,
        ));
    }
}
