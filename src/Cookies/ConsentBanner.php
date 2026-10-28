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

namespace Simplysmart\Admiro\Cookies;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\QueryException;
use Simplysmart\Admiro\Contracts\ConsentScripts;
use Simplysmart\Admiro\Contracts\CookieCatalog;
use Simplysmart\Admiro\Enums\CookieCategory;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Localization\LocaleRouting;
use Simplysmart\Admiro\Providers\CoreRoutesServiceProvider;
use Simplysmart\Admiro\Queries\Legal\LegalDocumentVersions;

/**
 * Stan banera cookies dla widoku (x-admiro::cookie-consent, x-admiro::cookie-links): baner jest potrzebny,
 * gdy opublikowano politykę cookies albo paczka zarejestrowała skrypt opcjonalny (same niezbędne cookies nie
 * wymagają zgody). Wersja polityki 0 = brak polityki; baza bez tabel (przed migracją) to brak polityki, żeby
 * strony nadal działały.
 */
final readonly class ConsentBanner
{
    public function __construct(
        private LegalDocumentVersions $versions,
        private CookieCatalog $catalog,
        private ConsentScripts $scripts,
        private Repository $config,
        private LocaleRouting $locales,
        private UrlGenerator $urls,
        private Application $app,
    ) {}

    public function state(): ?ConsentBannerState
    {
        if (! $this->config->boolean('admiro.cookies.enabled')) {
            return null;
        }

        try {
            $version = $this->versions->current(LegalDocumentType::Cookies)?->version;
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
            $this->config->string('admiro.cookies.cookie_name'),
            $this->config->integer('admiro.cookies.lifetime_days'),
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
