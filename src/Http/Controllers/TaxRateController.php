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

namespace Simplysmart\Admiro\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Admiro\Http\AdminAudit;
use Simplysmart\Admiro\Http\AdminContext;
use Simplysmart\Mainboard\Enums\TaxRateType;
use Simplysmart\Mainboard\Localization\Dictionary;
use Simplysmart\Mainboard\Localization\DictionaryChangeRejected;
use Simplysmart\Mainboard\Localization\DictionaryManagement;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Queries\Dictionaries\DictionaryList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Stawki VAT kraju (`settings.view`): historia stawek, nowa stawka od daty i zakończenie stawki (`settings.manage`).
 */
final readonly class TaxRateController
{
    public function __construct(
        private DictionaryList $dictionaries,
        private AdminContext $context,
        private Redirector $redirect,
    ) {}

    public function index(string $country): View
    {
        $current = $this->country($country);

        return view('admiro::dictionaries.tax-rates', [
            'country' => $current,
            'rates' => $this->dictionaries->taxRates($current),
            'types' => TaxRateType::cases(),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function store(Request $request, string $country, DictionaryManagement $management, AdminAudit $audit): RedirectResponse
    {
        $current = $this->country($country);
        $validated = $request->validate([
            'type' => ['required', Rule::enum(TaxRateType::class)],
            'rate' => ['nullable', 'numeric', 'between:0,100'],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $type = TaxRateType::from(is_string($validated['type']) ? $validated['type'] : '');
        $rate = $validated['rate'] ?? null;
        $note = $validated['note'] ?? null;

        try {
            $management->addTaxRate($current, $type, is_numeric($rate) ? (string) $rate : null, $this->date($validated['valid_from']), is_string($note) ? $note : null);
        } catch (DictionaryChangeRejected) {
            return $this->back($current)->withInput()->withErrors(['rate' => text('admiro::admin.tax_rate_rejected')]);
        }

        $audit->record('tax rate added', context: ['country' => $current->alpha_code_2, 'type' => $type->value, 'rate' => is_numeric($rate) ? (string) $rate : null]);

        return $this->back($current)->with('status', text('admiro::admin.tax_rate_saved'));
    }

    public function end(Request $request, string $country, string $rate, DictionaryManagement $management, AdminAudit $audit): RedirectResponse
    {
        $current = $this->country($country);
        $taxRate = $this->dictionaries->taxRate($current, $rate) ?? throw new NotFoundHttpException();
        $validated = $request->validate(['valid_to' => ['required', 'date_format:Y-m-d']]);

        try {
            $management->endTaxRate($taxRate, $this->date($validated['valid_to']));
        } catch (DictionaryChangeRejected) {
            return $this->back($current)->withErrors(['rate' => text('admiro::admin.tax_rate_rejected')]);
        }

        $audit->record('tax rate ended', context: ['country' => $current->alpha_code_2, 'tax_rate_id' => $taxRate->id]);

        return $this->back($current)->with('status', text('admiro::admin.tax_rate_saved'));
    }

    private function country(string $code): Country
    {
        $country = $this->dictionaries->find(Dictionary::Countries, strtoupper($code));

        return $country instanceof Country ? $country : throw new NotFoundHttpException();
    }

    private function date(mixed $value): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', is_string($value) ? $value : '')?->startOfDay() ?? throw new NotFoundHttpException();
    }

    private function back(Country $country): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('tax-rates.index'), ['country' => $country->alpha_code_2]);
    }
}
