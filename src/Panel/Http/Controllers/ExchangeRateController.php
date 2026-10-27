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

namespace Simplysmart\Admiro\Panel\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\ExchangeRates\ExchangeRateSync;
use Simplysmart\Admiro\ExchangeRates\ExchangeRatesRejected;
use Simplysmart\Admiro\ExchangeRates\ExchangeRatesUnavailable;
use Simplysmart\Admiro\ExchangeRates\ManualExchangeRates;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Queries\Dictionaries\ExchangeRatesOn;
use Simplysmart\Admiro\Queries\Dictionaries\LocalizationChoices;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Kursy walut w złotych (`settings.view`): lista z filtrem waluty i okresu; pobranie z tabeli A NBP za okres, kurs
 * ręczny i usunięcie kursu ręcznego (`settings.manage`, w dzienniku zdarzeń).
 */
final readonly class ExchangeRateController
{
    private const int PER_PAGE = 50;

    /**
     * Najdłuższy okres pobierania z panelu (dni) — dłuższe okresy komendą `admiro:exchange-rates:sync`.
     */
    private const int MAX_SYNC_DAYS = 366;

    public function __construct(
        private AdminContext $context,
        private LocalizationChoices $choices,
        private ExchangeRatesOn $rates,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(Request $request): View
    {
        $code = $request->query('currency');
        $currency = is_string($code) && $code !== '' ? $this->choices->currency($code) : null;

        return view('admiro::panel.exchange-rates.index', [
            'rates' => $this->rates->page($currency, $this->date($request->query('from')), $this->date($request->query('to')), self::PER_PAGE),
            'currencies' => $this->choices->currencies()->reject(static fn(Currency $entry): bool => $entry->alpha_code_3 === ExchangeRatesOn::BASE_CURRENCY),
            'currency' => $currency,
            'from' => is_string($request->query('from')) ? $request->query('from') : '',
            'to' => is_string($request->query('to')) ? $request->query('to') : '',
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function sync(Request $request, ExchangeRateSync $sync): RedirectResponse
    {
        $validated = $request->validate([
            'sync_from' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'sync_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:sync_from', 'before_or_equal:today'],
        ]);
        $from = $this->date($validated['sync_from']) ?? CarbonImmutable::today();
        $to = $this->date($validated['sync_to']) ?? CarbonImmutable::today();

        if ($from->diffInDays($to) > self::MAX_SYNC_DAYS) {
            return $this->back()->withInput()->withErrors(['sync_from' => text('admiro::admin.exchange_rates_period_too_long', ['days' => (string) self::MAX_SYNC_DAYS])]);
        }

        try {
            $saved = $sync->sync($from, $to);
        } catch (ExchangeRatesUnavailable) {
            return $this->back()->withInput()->withErrors(['sync_from' => text('admiro::admin.exchange_rates_unavailable')]);
        }

        $this->audit->record('exchange rates synced', context: ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'saved' => $saved]);

        return $this->back()->with('status', text('admiro::admin.exchange_rates_synced', ['count' => (string) $saved]));
    }

    public function store(Request $request, ManualExchangeRates $manual): RedirectResponse
    {
        $validated = $request->validate([
            'rate_currency' => ['required', 'string', 'size:3'],
            'rate_date' => ['required', 'date_format:Y-m-d'],
            'rate' => ['required', 'numeric', 'gt:0', 'max:1000000'],
        ]);
        $currency = $this->choices->currency(is_string($validated['rate_currency']) ? $validated['rate_currency'] : '');

        if ($currency === null || $currency->alpha_code_3 === ExchangeRatesOn::BASE_CURRENCY) {
            return $this->back()->withInput()->withErrors(['rate_currency' => text('admiro::admin.exchange_rate_unknown_currency')]);
        }

        $date = $this->date($validated['rate_date']) ?? CarbonImmutable::today();

        try {
            $manual->addManual($currency, $date, is_numeric($validated['rate']) ? (string) $validated['rate'] : '');
        } catch (ExchangeRatesRejected) {
            return $this->back()->withInput()->withErrors(['rate' => text('admiro::admin.exchange_rate_rejected')]);
        }

        $this->audit->record('exchange rate added', context: ['currency' => $currency->alpha_code_3, 'date' => $date->toDateString()]);

        return $this->back()->with('status', text('admiro::admin.exchange_rate_saved'));
    }

    public function destroy(string $rate, ManualExchangeRates $manual): RedirectResponse
    {
        $found = $this->rates->find($rate) ?? throw new NotFoundHttpException();

        try {
            $manual->removeManual($found);
        } catch (ExchangeRatesRejected) {
            return $this->back()->withErrors(['rate' => text('admiro::admin.exchange_rate_rejected')]);
        }

        $this->audit->record('exchange rate removed', context: ['currency' => $found->currency->alpha_code_3, 'date' => $found->rate_date->toDateString()]);

        return $this->back()->with('status', text('admiro::admin.exchange_rate_removed'));
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? CarbonImmutable::parse($value)->startOfDay() : null;
    }

    private function back(): RedirectResponse
    {
        $step = OnboardingStep::tryFrom(request()->string('onboarding')->toString());

        return $step === OnboardingStep::Currencies
            ? $this->redirect->route($this->context->zone()->routeName('onboarding.show'), ['step' => $step->value])
            : $this->redirect->route($this->context->zone()->routeName('exchange-rates.index'));
    }
}
