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
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Mainboard\Holidays\HolidaySync;
use Simplysmart\Mainboard\Holidays\HolidaysRejected;
use Simplysmart\Mainboard\Holidays\HolidaysUnavailable;
use Simplysmart\Mainboard\Holidays\ManualHolidays;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Mainboard\Queries\Dictionaries\HolidaysIn;
use Simplysmart\Mainboard\Queries\Dictionaries\LocalizationChoices;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Święta i dni wolne kraju w roku (`settings.view`; domyślnie kraj aplikacji i bieżący rok): pobranie z kalendarza
 * świąt, dzień wolny ręczny i usunięcie wpisu ręcznego (`settings.manage`, w dzienniku zdarzeń).
 */
final readonly class HolidayController
{
    private const int MIN_YEAR = 2000;

    private const int MAX_YEARS_AHEAD = 5;

    public function __construct(
        private AdminContext $context,
        private LocalizationChoices $choices,
        private HolidaysIn $holidays,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(Request $request, DefaultEntries $defaults): View
    {
        $code = $request->query('country');
        $country = (is_string($code) && $code !== '' ? $this->choices->country($code) : null) ?? $defaults->country();
        $year = $this->year($request->query('year'));

        return view('admiro::panel.holidays.index', [
            'countries' => $this->choices->countries(),
            'country' => $country,
            'year' => $year,
            'holidays' => $country === null ? collect() : $this->holidays->year($country, $year),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function sync(Request $request, HolidaySync $sync): RedirectResponse
    {
        $country = $this->country($request->input('country'));
        $year = $this->year($request->input('year'));

        try {
            $saved = $sync->sync($country, $year);
        } catch (HolidaysUnavailable) {
            return $this->back($country, $year)->withErrors(['holiday' => text('admiro::admin.holidays_unavailable')]);
        }

        $this->audit->record('holidays synced', context: ['country' => $country->alpha_code_2, 'year' => $year, 'saved' => $saved]);

        return $this->back($country, $year)->with('status', text('admiro::admin.holidays_synced', ['count' => (string) $saved]));
    }

    public function store(Request $request, ManualHolidays $manual): RedirectResponse
    {
        $country = $this->country($request->input('country'));
        $validated = $request->validate([
            'holiday_date' => ['required', 'date_format:Y-m-d'],
            'holiday_name' => ['required', 'string', 'max:255'],
        ]);
        $date = CarbonImmutable::parse(is_string($validated['holiday_date']) ? $validated['holiday_date'] : '');

        try {
            $manual->add($country, $date, is_string($validated['holiday_name']) ? $validated['holiday_name'] : '');
        } catch (HolidaysRejected) {
            return $this->back($country, $date->year)->withInput()->withErrors(['holiday' => text('admiro::admin.holiday_rejected')]);
        }

        $this->audit->record('holiday added', context: ['country' => $country->alpha_code_2, 'date' => $date->toDateString()]);

        return $this->back($country, $date->year)->with('status', text('admiro::admin.holiday_saved'));
    }

    public function destroy(string $country, string $holiday, ManualHolidays $manual): RedirectResponse
    {
        $found = $this->country($country);
        $entry = $this->holidays->find($found, $holiday) ?? throw new NotFoundHttpException();

        try {
            $manual->remove($entry);
        } catch (HolidaysRejected) {
            return $this->back($found, $entry->holiday_date->year)->withErrors(['holiday' => text('admiro::admin.holiday_rejected')]);
        }

        $this->audit->record('holiday removed', context: ['country' => $found->alpha_code_2, 'date' => $entry->holiday_date->toDateString()]);

        return $this->back($found, $entry->holiday_date->year)->with('status', text('admiro::admin.holiday_removed'));
    }

    private function country(mixed $code): Country
    {
        return (is_string($code) ? $this->choices->country(strtoupper($code)) : null) ?? throw new NotFoundHttpException();
    }

    private function year(mixed $value): int
    {
        $year = is_numeric($value) ? (int) $value : CarbonImmutable::today()->year;

        return max(self::MIN_YEAR, min($year, CarbonImmutable::today()->year + self::MAX_YEARS_AHEAD));
    }

    private function back(Country $country, int $year): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('holidays.index'), ['country' => $country->alpha_code_2, 'year' => $year]);
    }
}
