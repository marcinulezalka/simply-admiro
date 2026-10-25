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

namespace Simplysmart\Mainboard\Cookies;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\QueryException;
use Simplysmart\Mainboard\Contracts\ConsentScripts;
use Simplysmart\Mainboard\Contracts\CookieCatalog;
use Simplysmart\Mainboard\Enums\CookieCategory;
use Simplysmart\Mainboard\Localization\LocaleRouting;
use Simplysmart\Mainboard\Providers\CoreRoutesServiceProvider;
use Simplysmart\Mainboard\Queries\Cookies\CookiePolicyVersions;

/**
 * Stan banera cookies dla widoku (x-mainboard::cookie-consent, x-mainboard::cookie-links): baner jest potrzebny,
 * gdy opublikowano politykę cookies albo paczka zarejestrowała skrypt opcjonalny (same niezbędne cookies nie
 * wymagają zgody). Wersja polityki 0 = brak polityki; baza bez tabel (przed migracją) to brak polityki, żeby
 * strony nadal działały.
 */
final readonly class ConsentBanner
{
    public function __construct(
        private CookiePolicyVersions $versions,
        private CookieCatalog $catalog,
        private ConsentScripts $scripts,
        private Repository $config,
        private LocaleRouting $locales,
        private UrlGenerator $urls,
        private Application $app,
    ) {}

    public function state(): ?ConsentBannerState
    {
        if (! $this->config->boolean('mainboard.cookies.enabled')) {
            return null;
        }

        try {
            $version = $this->versions->current()?->version;
        } catch (QueryException) {
            $version = null;
        }
        $scripts = $this->scripts->all();

        if ($version === null && $scripts === []) {
            return null;
        }

        $cookies = [];

        foreach (CookieCategory::cases() as $category) {
            $cookies[$category->value] = array_values(array_filter($this->catalog->all(), static fn(CookieDescription $cookie): bool => $cookie->category === $category));
        }

        return new ConsentBannerState(
            $version ?? 0,
            $cookies,
            $scripts,
            $this->config->string('mainboard.cookies.cookie_name'),
            $this->config->integer('mainboard.cookies.lifetime_days'),
            $this->url('cookies.consent'),
            $version === null ? null : $this->url('cookies.policy'),
        );
    }

    /**
     * Adres trasy Core z prefiksem języka także tam, gdzie żądanie go nie ustawiło (np. strona błędu).
     */
    private function url(string $name): string
    {
        $locale = $this->app->getLocale();
        $parameters = $this->locales->isPrefixed() ? ['locale' => $this->locales->isEnabled($locale) ? $locale : $this->locales->default()] : [];

        return $this->urls->route(CoreRoutesServiceProvider::NAME_PREFIX . $name, $parameters);
    }
}
