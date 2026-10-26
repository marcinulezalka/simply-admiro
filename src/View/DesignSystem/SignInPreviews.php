<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Admiro\View\DesignSystem;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Simplysmart\Admiro\Auth\PinLength;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Enums\DesignSystemView;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Dane podglądów prawdziwych ekranów logowania (`admiro::auth.*`) w design systemie: pierwsza strefa z logowaniem,
 * jej ustawienia (długość kodu, czas ważności, PIN), błędy pól tak, jak pokazuje je logowanie (błędny kod, zbyt wiele
 * prób, wygasła sesja logowania, słaby PIN) i stany wariantów: otwarta klawiatura PIN-u (`keypadOpen`), rozwinięta
 * sekcja zapasowego adresu (`recoveryOpen`) i oczekujący kod resetu PIN-u (`codePending`). Bez strefy z logowaniem
 * podglądu nie ma (404).
 */
final readonly class SignInPreviews
{
    public const string EMAIL = 'anna@example.com';

    public const int LIMIT_SECONDS = 60;

    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * @return array<string, mixed>
     *
     * @throws NotFoundHttpException Gdy żadna strefa nie ma logowania.
     */
    public function data(DesignSystemView $view): array
    {
        if (! $view->isZoneSignIn()) {
            return ['errors' => $this->errors([])];
        }

        $zone = $this->zone() ?? throw new NotFoundHttpException();
        $auth = $zone->auth();

        return [
            'zone' => $zone,
            'email' => self::EMAIL,
            'codeLength' => $auth->codeLength,
            'validForMinutes' => $auth->codeTtlMinutes,
            'resendIn' => 0,
            'rememberDeviceDays' => $auth->trustedDevicesEnabled ? $auth->trustedDeviceLifetimeDays : null,
            'pinLength' => $view === DesignSystemView::SignInPinSetup ? $auth->pinLength : PinLength::accepted(),
            'keypadOpen' => $view === DesignSystemView::SignInPinKeypad,
            'recoveryOpen' => $view === DesignSystemView::ForgotPinRecovery,
            'codePending' => $view === DesignSystemView::ForgotPinCode,
            'errors' => $this->errors(match ($view) {
                DesignSystemView::SignInCodeInvalid => ['code' => text('sign_in.failure')],
                DesignSystemView::SignInPinLimit => ['pin' => text('sign_in.too_many_attempts', ['seconds' => self::LIMIT_SECONDS])],
                DesignSystemView::SignInPinSetup => ['pin' => text('sign_in.weak_pin')],
                DesignSystemView::SignInExpired => ['email' => text('sign_in.expired')],
                default => [],
            }),
        ];
    }

    private function zone(): ?ZoneDefinition
    {
        foreach ($this->zones->all() as $zone) {
            if ($zone->requiresLogin()) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * @param array<string, string> $messages
     */
    private function errors(array $messages): ViewErrorBag
    {
        return (new ViewErrorBag())->put('default', new MessageBag($messages));
    }
}
