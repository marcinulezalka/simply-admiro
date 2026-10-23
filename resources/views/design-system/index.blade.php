{{-- Strona design systemu (DesignSystemController, tylko poza produkcją): tokeny odczytane z tokens.css (View\DesignTokens) i komponenty Core na żywo. Próbki tokenów mają klasy generowane z tych samych tokenów w <style> z nonce CSP, więc strona nie ma własnej listy wartości. Sekcja kolorystyk ma żywy przełącznik (wszystkie Enums\Palette, zakres design-system). $tokens: DesignTokens, $icons: list<string>. --}}
@php
    $colors = $tokens->group('color');
    $paletteTokens = $tokens->group('palette');
    $palettes = \Simplysmart\Mainboard\Enums\Palette::cases();
    $sections = ['colors', 'palettes', 'typography', 'radius', 'shadow', 'space', 'buttons', 'forms', 'feedback', 'periods', 'surfaces', 'icons', 'brand'];
    $highlight = \Simplysmart\Mainboard\View\BrandNames::highlight(...);
@endphp
<x-mainboard::page :title="text('design_system.title')" :description="text('design_system.lead')" robots="noindex, nofollow" :palettes="$palettes" palette-scope="design-system">
    <x-slot:head>
        <x-mainboard::styles :files="[...\Simplysmart\Mainboard\View\CoreAssets::STYLESHEETS, 'css/design-system.css']" />
        <x-mainboard::scripts :files="['js/input-mask.js']" />
        <style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
@foreach ([...$colors, ...$paletteTokens] as $token)
.ss-ds-chip--{{ $token->key }}-light { background: {{ $token->light }}; }
.ss-ds-chip--{{ $token->key }}-dark { background: {{ $token->dark }}; }
@endforeach
@foreach ($tokens->group('radius') as $token)
.ss-ds-radius--{{ $token->key }} { border-radius: var({{ $token->name }}); }
@endforeach
@foreach ($tokens->group('shadow') as $token)
.ss-ds-shadow--{{ $token->key }} { box-shadow: var({{ $token->name }}); }
@endforeach
@foreach ($tokens->group('space') as $token)
.ss-ds-space--{{ $token->key }} { width: var({{ $token->name }}); }
@endforeach
        </style>
    </x-slot:head>

    <div class="ss-ds">
        <header class="ss-ds__header">
            <x-mainboard::logo class="ss-ds__logo" />
            <div class="ss-ds__heading">
                <h1 class="ss-ds__title">{{ text('design_system.title') }}</h1>
                <span class="ss-badge">{{ text('design_system.not_for_production') }}</span>
                <x-mainboard::theme-toggle />
                <x-mainboard::palette-switch :palettes="$palettes" />
            </div>
            <p class="ss-ds__lead ss-muted">{{ text('design_system.lead') }}</p>
        </header>

        <div class="ss-ds__layout">
            <x-mainboard::design-system.nav :sections="$sections" />

            <main class="ss-ds__sections">
                <section class="ss-card" id="colors" aria-labelledby="colors-title">
                    <h2 class="ss-ds__section-title" id="colors-title">{{ text('design_system.sections.colors') }}</h2>
                    <p class="ss-ds__section-lead ss-muted">{{ text('design_system.sections.colors_lead') }}</p>
                    <div class="ss-ds__table-wrap">
                        <table class="ss-ds__table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ text('design_system.token') }}</th>
                                    <th scope="col">{{ text('design_system.light') }}</th>
                                    <th scope="col">{{ text('design_system.dark') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($colors as $token)
                                    <tr>
                                        <th scope="row"><code>{{ $token->name }}</code></th>
                                        <td><span class="ss-ds__value"><span class="ss-ds-chip ss-ds-chip--{{ $token->key }}-light" aria-hidden="true"></span><code>{{ $token->light }}</code></span></td>
                                        <td><span class="ss-ds__value"><span class="ss-ds-chip ss-ds-chip--{{ $token->key }}-dark" aria-hidden="true"></span><code>{{ $token->dark }}</code></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="ss-card" id="palettes" aria-labelledby="palettes-title">
                    <h2 class="ss-ds__section-title" id="palettes-title">{{ text('design_system.sections.palettes') }}</h2>
                    <p class="ss-ds__section-lead ss-muted">{{ text('design_system.sections.palettes_lead') }}</p>
                    <div class="ss-ds__row">
                        <button class="ss-button ss-button--primary" type="button">{{ text('design_system.sample.primary') }}</button>
                        <a class="ss-link" href="#palettes">{{ text('design_system.sample.link') }}</a>
                        <span class="ss-card ss-card--inverse ss-ds__palette-inverse">{{ text('design_system.sample.palette_inverse') }}</span>
                    </div>
                    <p class="ss-ds__row"><code>&lt;html data-palette="…"&gt;</code> <code>palettes</code> <code>x-mainboard::palette-switch</code></p>
                    <div class="ss-ds__table-wrap">
                        <table class="ss-ds__table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ text('design_system.token') }}</th>
                                    <th scope="col">{{ text('design_system.light') }}</th>
                                    <th scope="col">{{ text('design_system.dark') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paletteTokens as $token)
                                    <tr>
                                        <th scope="row"><code>{{ $token->name }}</code></th>
                                        <td><span class="ss-ds__value"><span class="ss-ds-chip ss-ds-chip--{{ $token->key }}-light" aria-hidden="true"></span><code>{{ $token->light }}</code></span></td>
                                        <td><span class="ss-ds__value"><span class="ss-ds-chip ss-ds-chip--{{ $token->key }}-dark" aria-hidden="true"></span><code>{{ $token->dark }}</code></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="ss-card" id="typography" aria-labelledby="typography-title">
                    <h2 class="ss-ds__section-title" id="typography-title">{{ text('design_system.sections.typography') }}</h2>
                    <div class="ss-ds__stack">
                        <p class="ss-ds__type-label ss-muted">text-2xl · 600</p>
                        <p class="ss-ds__type-page">{{ text('design_system.sample.page_title') }}</p>
                        <p class="ss-ds__type-label ss-muted">text-base · 600</p>
                        <p class="ss-ds__type-card">{{ text('design_system.sample.card_title') }}</p>
                        <p class="ss-ds__type-label ss-muted">text-sm · --ss-color-text</p>
                        <p class="ss-ds__type-value">{{ text('design_system.sample.value') }}</p>
                        <p class="ss-ds__type-label ss-muted">text-xs · --ss-color-muted</p>
                        <p class="ss-ds__type-meta ss-muted">{{ text('design_system.sample.label') }}</p>
                    </div>
                </section>

                <section class="ss-card" id="radius" aria-labelledby="radius-title">
                    <h2 class="ss-ds__section-title" id="radius-title">{{ text('design_system.sections.radius') }}</h2>
                    <ul class="ss-ds__samples">
                        @foreach ($tokens->group('radius') as $token)
                            <li class="ss-ds__sample">
                                <span class="ss-ds-box ss-ds-radius--{{ $token->key }}" aria-hidden="true"></span>
                                <code>{{ $token->name }}</code>
                                <span class="ss-muted">{{ $token->light }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="ss-card" id="shadow" aria-labelledby="shadow-title">
                    <h2 class="ss-ds__section-title" id="shadow-title">{{ text('design_system.sections.shadow') }}</h2>
                    <p class="ss-ds__section-lead ss-muted">{{ text('design_system.sections.shadow_lead') }}</p>
                    <ul class="ss-ds__samples">
                        @foreach ($tokens->group('shadow') as $token)
                            <li class="ss-ds__sample">
                                <span class="ss-ds-box ss-ds-box--raised ss-ds-shadow--{{ $token->key }}" aria-hidden="true"></span>
                                <code>{{ $token->name }}</code>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="ss-card" id="space" aria-labelledby="space-title">
                    <h2 class="ss-ds__section-title" id="space-title">{{ text('design_system.sections.space') }}</h2>
                    <ul class="ss-ds__spaces">
                        @foreach ($tokens->group('space') as $token)
                            <li class="ss-ds__space">
                                <code>{{ $token->name }}</code>
                                <span class="ss-muted">{{ $token->light }}</span>
                                <span class="ss-ds-bar ss-ds-space--{{ $token->key }}" aria-hidden="true"></span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="ss-card" id="buttons" aria-labelledby="buttons-title">
                    <h2 class="ss-ds__section-title" id="buttons-title">{{ text('design_system.sections.buttons') }}</h2>
                    <p class="ss-ds__section-lead ss-muted">{{ text('design_system.sections.buttons_lead') }}</p>
                    <div class="ss-ds__row">
                        <button class="ss-button ss-button--primary" type="button">{{ text('design_system.sample.primary') }}</button>
                        <button class="ss-button" type="button">{{ text('design_system.sample.secondary') }}</button>
                        <button class="ss-button" type="button" disabled>{{ text('design_system.sample.disabled') }}</button>
                        <a class="ss-link" href="#buttons">{{ text('design_system.sample.link') }}</a>
                    </div>
                    <div class="ss-ds__row ss-ds__spaced">
                        <button class="ss-button ss-button--primary" type="button"><x-mainboard::icon name="refresh" class="ss-button__icon" />{{ text('design_system.sample.icon_before') }}</button>
                        <button class="ss-button" type="button">{{ text('design_system.sample.icon_after') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
                        <button class="ss-button ss-button--primary ss-button--pill" type="button"><x-mainboard::icon name="refresh" class="ss-button__icon" />{{ text('design_system.sample.pill') }}</button>
                        <button class="ss-button ss-button--pill" type="button">{{ text('design_system.sample.pill') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
                    </div>
                    <div class="ss-ds__narrow">
                        <button class="ss-button ss-button--primary ss-button--block" type="button">.ss-button--block<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
                    </div>
                </section>

                <section class="ss-card" id="forms" aria-labelledby="forms-title">
                    <h2 class="ss-ds__section-title" id="forms-title">{{ text('design_system.sections.forms') }}</h2>
                    <div class="ss-form ss-ds__narrow">
                        <x-mainboard::input id="ds-email" :label="text('design_system.sample.field')" icon="mail" type="email" :placeholder="text('placeholders.email')" :hint="text('design_system.sample.placeholder_hint')" />
                        <x-mainboard::input id="ds-email-error" :label="text('design_system.sample.field_error')" icon="mail" type="email" value="jan@" :error="text('design_system.sample.error')" />
                        <x-mainboard::input id="ds-name" :label="text('design_system.sample.person')" icon="user" autocomplete="off" :placeholder="text('placeholders.full_name')" />
                        <x-mainboard::input id="ds-company" :label="text('design_system.demo.client_name')" icon="building" autocomplete="off" :placeholder="text('placeholders.company')" />
                        <x-mainboard::input id="ds-date" :label="text('design_system.sample.date')" icon="calendar" type="date" />
                        @foreach (\Simplysmart\Mainboard\Enums\InputMask::cases() as $mask)
                            <x-mainboard::input :id="'ds-mask-' . $mask->value" :label="text('design_system.sample.masks.' . $mask->value)" :mask="$mask" />
                        @endforeach
                        <div class="ss-field">
                            <label class="ss-field__label" for="ds-code">{{ text('design_system.sample.code') }}</label>
                            <input class="ss-input ss-input--code" id="ds-code" inputmode="numeric" value="15935748">
                        </div>
                        <div>
                            <x-mainboard::switch id="ds-switch-on" :label="text('design_system.sample.check')" :hint="text('design_system.sample.switch_hint')" :checked="true" />
                            <x-mainboard::switch id="ds-switch-off" :label="text('design_system.sample.switch_off')" />
                            <x-mainboard::switch id="ds-switch-disabled" :label="text('design_system.sample.disabled')" disabled />
                        </div>
                    </div>
                </section>

                <section class="ss-card" id="feedback" aria-labelledby="feedback-title">
                    <h2 class="ss-ds__section-title" id="feedback-title">{{ text('design_system.sections.feedback') }}</h2>
                    <div class="ss-ds__stack ss-ds__narrow">
                        <p class="ss-notice">{{ text('design_system.sample.notice') }}</p>
                        <div class="ss-ds__row">
                            <x-mainboard::avatar :name="text('design_system.demo.account')" size="sm" />
                            <x-mainboard::avatar :name="text('design_system.demo.account')" />
                            <x-mainboard::avatar :name="text('design_system.demo.account')" :accent="true" />
                            <x-mainboard::avatar :name="text('design_system.demo.account')" size="lg" />
                        </div>
                        <div class="ss-ds__row">
                            <span class="ss-avatars">
                                @foreach (\Simplysmart\Mainboard\Enums\AvatarColor::cases() as $color)
                                    <x-mainboard::avatar :name="$color->label()" :color="$color" :label="true" />
                                @endforeach
                            </span>
                            <x-mainboard::avatar-colors :name="text('design_system.demo.account')" :current="\Simplysmart\Mainboard\Enums\AvatarColor::Blue" field="ds-avatar-color" />
                            <span class="ss-person">
                                <x-mainboard::avatar :name="text('design_system.demo.account')" size="sm" />
                                <span class="ss-person__text"><span>{{ text('design_system.demo.account') }}</span><span class="ss-muted">{{ text('design_system.demo.account_subtitle') }}</span></span>
                            </span>
                        </div>
                        <x-mainboard::toasts :inline="true" :toasts="\Simplysmart\Mainboard\View\Demo\DemoNotifications::toasts(route('mainboard.design-system.view', \Simplysmart\Mainboard\Enums\DesignSystemView::Notifications))" />
                        <p class="ss-field__error">{{ text('design_system.sample.error') }}</p>
                        <div class="ss-ds__row">
                            <span class="ss-badge">{{ text('welcome.mainboard_version', ['version' => \Simplysmart\Mainboard\View\CoreVersion::current()]) }}</span>
                            <span class="ss-badge">{{ text('design_system.not_for_production') }}</span>
                        </div>
                    </div>
                </section>

                <section class="ss-card" id="periods" aria-labelledby="periods-title">
                    <h2 class="ss-ds__section-title" id="periods-title">{{ text('design_system.sections.periods') }}</h2>
                    <div class="ss-ds__stack">
                        <div class="ss-period">
                            <ul class="ss-tabs">
                                @foreach (['day', 'week', 'month'] as $range)
                                    <li><a class="ss-tabs__link" href="#periods" @if ($loop->first) aria-current="page" @endif>{{ text('design_system.sample.ranges.' . $range) }}</a></li>
                                @endforeach
                            </ul>
                            <div class="ss-period__nav">
                                <a class="ss-icon-button" href="#periods"><x-mainboard::icon name="arrow-left" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('design_system.sample.previous') }}</span></a>
                                <p class="ss-period__label">{{ local_date(\Simplysmart\Mainboard\View\Demo\DemoInvoices::TODAY, 'dddd, LL') }}</p>
                                <a class="ss-icon-button" href="#periods"><x-mainboard::icon name="arrow-right" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('design_system.sample.next') }}</span></a>
                            </div>
                        </div>
                        <div class="ss-ds__row">
                            @foreach ([3, 8, 10] as $value)
                                <div class="ss-ds__meter">
                                    <span class="ss-muted">{{ local_number($value) }} / {{ local_number(10) }}</span>
                                    <meter class="ss-meter" min="0" max="10" low="6" high="9" optimum="0" value="{{ $value }}">{{ $value }}</meter>
                                </div>
                            @endforeach
                        </div>
                        <div class="ss-calendar ss-ds__calendar">
                            <div class="ss-calendar__row">
                                @foreach (range(1, 7) as $weekday)
                                    <span class="ss-calendar__weekday">{{ local_day($weekday, 'dd') }}</span>
                                @endforeach
                            </div>
                            <div class="ss-calendar__row">
                                @foreach (range(19, 25) as $date)
                                    <div @class(['ss-calendar__day', 'ss-cell--open' => $date < 24, 'ss-cell--closed' => $date >= 24, 'ss-calendar__day--today' => $date === 23]) @if ($date === 23) aria-current="date" @endif>
                                        <span class="ss-calendar__date">{{ $date }}</span>
                                        <span class="ss-calendar__note">{{ $date < 24 ? text('design_system.sample.available') : text('design_system.sample.unavailable') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <p class="ss-legend">
                            <span class="ss-legend__item"><span class="ss-legend__swatch ss-cell--open" aria-hidden="true"></span>{{ text('design_system.sample.available') }}</span>
                            <span class="ss-legend__item"><span class="ss-legend__swatch ss-cell--closed" aria-hidden="true"></span>{{ text('design_system.sample.unavailable') }}</span>
                        </p>
                    </div>
                </section>

                <section class="ss-card" id="surfaces" aria-labelledby="surfaces-title">
                    <h2 class="ss-ds__section-title" id="surfaces-title">{{ text('design_system.sections.surfaces') }}</h2>
                    <div class="ss-frame ss-frame--row ss-ds__frame">
                        <div class="ss-card ss-card--tinted ss-ds__frame-card">
                            <p class="ss-ds__frame-title"><span class="ss-muted">{{ text('design_system.sample.frame_kicker') }}</span> {{ text('design_system.sample.frame_title') }}</p>
                            <p class="ss-muted">{{ text('design_system.sample.frame_text') }}</p>
                            <p class="ss-ds__row"><code>.ss-frame</code> <code>.ss-card--tinted</code> <code>--ss-radius-nested</code></p>
                            <div class="ss-ds__row ss-ds__push">
                                <button class="ss-button ss-button--primary ss-button--pill" type="button"><x-mainboard::icon name="refresh" class="ss-button__icon" />{{ text('design_system.sample.start') }}</button>
                            </div>
                        </div>
                        <div class="ss-card ss-card--inverse ss-card--elevated ss-ds__frame-card">
                            <p class="ss-ds__frame-title">{{ text('design_system.sample.inverse_title') }}</p>
                            <p class="ss-muted">{{ text('design_system.sample.inverse_text') }}</p>
                            <p class="ss-ds__row"><code>.ss-card--inverse</code> <code>.ss-card--elevated</code></p>
                            <div class="ss-ds__row ss-ds__push ss-ds__between">
                                <span class="ss-badge ss-ds__badge-inverse"><x-mainboard::icon name="shield-check" class="ss-badge__icon" />{{ text('design_system.sample.secured') }}</span>
                                <button class="ss-button ss-button--primary ss-button--pill" type="button">{{ text('design_system.sample.more') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
                            </div>
                        </div>
                    </div>
                    <div class="ss-card ss-ds__demo-card">
                            <p class="ss-ds__type-card">.ss-card</p>
                            <p class="ss-muted">{{ text('design_system.sample.card') }}</p>
                            <div class="ss-card ss-card--muted">
                                <p class="ss-ds__type-card">.ss-card--muted</p>
                                <p class="ss-muted">{{ text('design_system.sample.card_muted') }}</p>
                            </div>
                    </div>
                </section>

                <section class="ss-card" id="icons" aria-labelledby="icons-title">
                    <h2 class="ss-ds__section-title" id="icons-title">{{ text('design_system.sections.icons') }}</h2>
                    <ul class="ss-ds__icons">
                        @foreach ($icons as $icon)
                            <li class="ss-ds__icon">
                                <x-mainboard::icon :name="$icon" class="ss-icon--tile" />
                                <code>{{ $icon }}</code>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="ss-card" id="brand" aria-labelledby="brand-title">
                    <h2 class="ss-ds__section-title" id="brand-title">{{ text('design_system.sections.brand') }}</h2>
                    <div class="ss-ds__stack">
                        <div class="ss-ds__row">
                            <x-mainboard::logo class="ss-ds__brand-logo" />
                            <span class="ss-welcome__laravel">{!! \Simplysmart\Mainboard\View\CoreAssets::contents('img/vendor/laravel.svg') !!}</span>
                        </div>
                        <p>{{ $highlight(text('design_system.sample.brand_text')) }}</p>
                        <p class="ss-ds__row">
                            <span class="ss-muted">{{ text('design_system.sample.flags') }}</span>
                            <x-mainboard::flag icon="pl" /> <x-mainboard::flag icon="gb" /> <x-mainboard::flag icon="de" />
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-mainboard::page>
