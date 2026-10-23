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

namespace Simplysmart\Mainboard\View;

use Simplysmart\Mainboard\Enums\ToastType;

/**
 * Toast (alert aplikacji na środku ekranu): typ, treść, opcjonalny tytuł i akcja. Akcje flashują toasty do sesji
 * pod kluczem {@see self::SESSION_KEY} (toArray()), layout aplikacji odczytuje je przez fromSession().
 */
final readonly class Toast
{
    public const string SESSION_KEY = 'toasts';

    public function __construct(
        public ToastType $type,
        public string $message,
        public ?string $title = null,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {}

    /**
     * Tytuł widoczny w toaście: własny albo nazwa typu („Błąd”, „Powodzenie”…).
     */
    public function heading(): string
    {
        return $this->title ?? $this->type->label();
    }

    public function hasAction(): bool
    {
        return $this->actionLabel !== null && $this->actionUrl !== null;
    }

    /**
     * @return array{type: string, message: string, title: ?string, action_label: ?string, action_url: ?string}
     */
    public function toArray(): array
    {
        return ['type' => $this->type->value, 'message' => $this->message, 'title' => $this->title, 'action_label' => $this->actionLabel, 'action_url' => $this->actionUrl];
    }

    /**
     * Toasty z wartości sesji (lista tablic z toArray()); nieznany typ albo brak treści są pomijane.
     *
     * @return list<self>
     */
    public static function fromSession(mixed $value): array
    {
        $toasts = [];

        foreach (is_array($value) ? $value : [] as $entry) {
            $type = is_array($entry) && is_string($entry['type'] ?? null) ? ToastType::tryFrom($entry['type']) : null;

            if ($type !== null && is_string($entry['message'] ?? null)) {
                $toasts[] = new self(
                    $type,
                    $entry['message'],
                    is_string($entry['title'] ?? null) ? $entry['title'] : null,
                    is_string($entry['action_label'] ?? null) ? $entry['action_label'] : null,
                    is_string($entry['action_url'] ?? null) ? $entry['action_url'] : null,
                );
            }
        }

        return $toasts;
    }
}
