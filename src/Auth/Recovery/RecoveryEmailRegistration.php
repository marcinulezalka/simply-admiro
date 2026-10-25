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

namespace Simplysmart\Admiro\Auth\Recovery;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\Auth\EmailAddress;
use Simplysmart\Admiro\Auth\Events\RecoveryEmailChanged;
use Simplysmart\Admiro\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Admiro\Auth\SignInLimit;
use Simplysmart\Admiro\Contracts\AccountStore;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Mail\MailOrganization;
use Simplysmart\Admiro\Mail\SecurityCodeMail;
use Simplysmart\Admiro\Models\ZoneAccount;

/**
 * Zapasowy adres e-mail zalogowanego konta (strona „Bezpieczeństwo”): kod na nowy adres (inny niż główny),
 * potwierdzenie kodem zapisuje adres, można go też usunąć. Każda zmiana w dzienniku (RecoveryEmailChanged, bez
 * adresu). Wysyłki i sprawdzenia kodu mają limity logowania strefy.
 */
final readonly class RecoveryEmailRegistration
{
    public const string EMAIL_FIELD = 'recovery_email';

    public const string CODE_FIELD = 'recovery_code';

    private const string PAYLOAD_EMAIL = 'email';

    public function __construct(
        private SessionCodes $codes,
        private LoginAttemptLimiter $limiter,
        private AccountStore $store,
        private CurrentZone $zone,
        private Mailer $mailer,
        private MailOrganization $organizations,
        private Dispatcher $events,
    ) {}

    /**
     * @throws ValidationException Gdy adres to adres główny albo limit wysyłek jest wyczerpany.
     */
    public function sendCode(ZoneAccount $account, string $email, Request $request): void
    {
        $email = EmailAddress::normalize($email);

        if ($email === EmailAddress::normalize($account->emailAddress())) {
            throw ValidationException::withMessages([self::EMAIL_FIELD => text('account.recovery.same_as_primary')]);
        }

        $this->limiter->attempt(SignInLimit::AccountCodeRequests, $account->emailAddress(), self::EMAIL_FIELD);
        $zone = $this->zone->get();
        $code = $this->codes->issue($request->session(), SecurityCodePurpose::RecoveryEmail, [self::PAYLOAD_EMAIL => $email]);

        $this->mailer->to($email)->queue(new SecurityCodeMail($zone->name, $this->organizations->for($zone, $account), $account->displayName(), $code, $zone->auth()->codeTtlMinutes, SecurityCodePurpose::RecoveryEmail));
    }

    /**
     * Adres czekający na potwierdzenie kodem; null, gdy kodu nie wysłano albo wygasł.
     */
    public function pending(Request $request): ?string
    {
        return $this->codes->pending($request->session(), SecurityCodePurpose::RecoveryEmail)[self::PAYLOAD_EMAIL] ?? null;
    }

    /**
     * @throws ValidationException Gdy kod jest błędny albo wygasł, albo limit sprawdzeń jest wyczerpany.
     */
    public function confirm(ZoneAccount $account, string $code, Request $request): void
    {
        $this->limiter->attempt(SignInLimit::AccountCodeChecks, (string) AccountKey::of($account), self::CODE_FIELD);
        $email = $this->codes->verify($request->session(), SecurityCodePurpose::RecoveryEmail, $code)[self::PAYLOAD_EMAIL] ?? null;

        if ($email === null) {
            throw ValidationException::withMessages([self::CODE_FIELD => text('account.code_invalid')]);
        }

        $this->store->setRecoveryEmail($account, $email);
        $this->events->dispatch(new RecoveryEmailChanged($this->zone->get()->name, $account, true, $request->ip()));
    }

    public function remove(ZoneAccount $account, Request $request): void
    {
        $this->codes->forget($request->session(), SecurityCodePurpose::RecoveryEmail);

        if ($account->recoveryEmail() === null) {
            return;
        }

        $this->store->setRecoveryEmail($account, null);
        $this->events->dispatch(new RecoveryEmailChanged($this->zone->get()->name, $account, false, $request->ip()));
    }
}
