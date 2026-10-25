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

namespace Simplysmart\Mainboard\Auth\Recovery;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\AccountColumns;
use Simplysmart\Mainboard\Auth\EmailAddress;
use Simplysmart\Mainboard\Auth\RevokeAccountAccess;
use Simplysmart\Mainboard\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Mail\MailOrganization;
use Simplysmart\Mainboard\Mail\SecurityCodeMail;

/**
 * Reset zapomnianego PIN-u przez zapasowy adres e-mail (strona „Nie pamiętam PIN-u”): gość podaje adres konta, kod
 * idzie na potwierdzony zapasowy adres, a poprawny kod usuwa PIN, zdejmuje blokadę i unieważnia sesje oraz zaufane
 * urządzenia (jak reset przez administratora). Nowy PIN ustawia się przy logowaniu kodem z głównego adresu, więc
 * do przejęcia konta potrzeba obu skrzynek. Odpowiedź jest taka sama dla każdego adresu (konto bez zapasowego
 * adresu albo nieznane dostaje kod, którego nikt nie otrzyma), żeby nie zdradzać, czy konto istnieje.
 */
final readonly class RecoveryPinReset
{
    public const string EMAIL_FIELD = 'email';

    public const string CODE_FIELD = 'code';

    private const string PAYLOAD_EMAIL = 'email';

    public function __construct(
        private SessionCodes $codes,
        private LoginAttemptLimiter $limiter,
        private AccountLocator $accounts,
        private AccountStore $store,
        private RevokeAccountAccess $revoke,
        private CurrentZone $zone,
        private Mailer $mailer,
        private MailOrganization $organizations,
    ) {}

    /**
     * @throws ValidationException Gdy limit wysyłek jest wyczerpany.
     */
    public function sendCode(string $email, Request $request): void
    {
        $email = EmailAddress::normalize($email);
        $this->limiter->attempt(SignInLimit::CodeRequests, $email . '|' . $request->ip(), self::EMAIL_FIELD);
        $this->limiter->attempt(SignInLimit::AccountCodeRequests, $email, self::EMAIL_FIELD);

        $zone = $this->zone->get();
        $code = $this->codes->issue($request->session(), SecurityCodePurpose::PinReset, [self::PAYLOAD_EMAIL => $email]);
        $account = $this->accounts->findByEmail($email);
        $recoveryEmail = $account?->recoveryEmail();

        if ($account !== null && $recoveryEmail !== null && $account->getAttribute(AccountColumns::IS_ACTIVE) === true) {
            $this->mailer->to($recoveryEmail)->queue(new SecurityCodeMail($zone->name, $this->organizations->for($zone, $account), $account->displayName(), $code, $zone->auth()->codeTtlMinutes, SecurityCodePurpose::PinReset));
        }
    }

    /**
     * Czy w tej sesji czeka kod resetu (formularz kodu zamiast formularza adresu).
     */
    public function isPending(Request $request): bool
    {
        return $this->codes->pending($request->session(), SecurityCodePurpose::PinReset) !== null;
    }

    public function cancel(Request $request): void
    {
        $this->codes->forget($request->session(), SecurityCodePurpose::PinReset);
    }

    /**
     * @throws ValidationException Gdy kod jest błędny albo wygasł, albo limit sprawdzeń jest wyczerpany.
     */
    public function confirm(string $code, Request $request): void
    {
        $this->limiter->attempt(SignInLimit::CodeChecks, (string) $request->ip(), self::CODE_FIELD);
        $email = $this->codes->verify($request->session(), SecurityCodePurpose::PinReset, $code)[self::PAYLOAD_EMAIL] ?? null;
        $account = $email === null ? null : $this->accounts->findByEmail($email);

        if ($account === null || $account->recoveryEmail() === null || $account->getAttribute(AccountColumns::IS_ACTIVE) !== true) {
            throw ValidationException::withMessages([self::CODE_FIELD => text('account.code_invalid')]);
        }

        $this->store->clearPin($account);
        $this->store->unlock($account);
        $this->revoke->handle($this->zone->get(), $account, AccessRevocationReason::PinResetWithRecoveryEmail);
    }
}
