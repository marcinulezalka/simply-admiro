{{-- Layout aplikacji z menu bocznym (docs/design-system.md §5). Tło: duży logotyp simplysmart wtopiony w płótno (x-admiro::app.backdrop). Menu po lewej przy krawędziach okna (stała szerokość, dłuższe pozycje i nazwy zawijają się): u góry awatar z imieniem i nazwiskiem (albo nazwą) i drugą linią (organizacja, rola) — kliknięcie otwiera menu konta; pod nim zwijanie menu, przełącznik kontekstu, ustawienia (ikona suwaków, settings->url; bieżące na stronach ustawień) i dzwonek powiadomień; pozycje menu w grupach — każda grupa to osobna lista na jasnym tle (.ss-sidebar__section) z nazwą u góry, z większymi odstępami między grupami, bez kresek (pozycja z podpozycjami — NavigationItem::$children — jest gałęzią rozwijaną <details> z chevronem, podpozycje połączone liniami drzewa; w menu zwiniętym do ikon zamiast niej odnośnik pozycji); na dole przycisk „Wyloguj” (formularz POST na signOut) i subtelne, wyśrodkowane logo simplysmart (35% szerokości), które odsłania pełnoszeroką stopkę strony simplysmart jak na logowaniu (x-admiro::brand-footer: powłoka .ss-shell przesuwa się w górę nad stopką; na ekranach bez stałego menu stopka na końcu strony). Na stronie ustawień (settings->active) menu pokazuje grupy ustawień z powrotem do aplikacji i tytułem „Ustawienia” — navigation zawiera wtedy grupy ustawień. Menu zwija się do ikon (stan w przeglądarce), na telefonie wysuwa się z boku (przycisk w top barze). Top bar: globalna wyszukiwarka na środku (skrót „/”; z zakresami „Szukaj w” — search->scopes, checkboksy w menu przy polu — i podpowiedziami w trakcie pisania z search->suggestUrl, js/global-search.js; formularze z data-ss-autosubmit wysyłają się po zmianie pola, js/autosubmit.js). navigation: list<View\Navigation\NavigationGroup>; settings: ?View\Navigation\SettingsMenu (null = bez ustawień, np. podgląd w design systemie); search: ?View\Navigation\SearchBox; account: ?array{name: string, subtitle?: string, image?: string, color?: Enums\AvatarColor, label?: string, status?: ?string, active?: bool, social?: list<array{url: string, label: string, icon: string}>, items?: list<View\Navigation\NavigationItem>} (label: opis konta dla czytników ekranu) (bez koloru awatar w kolorze akcentu); signOut: ?string (adres wylogowania: formularz POST na dole menu); contexts: ?list<View\Navigation\NavigationGroup> (przełącznik kontekstu, np. organizacja i rola; pozycje NavigationItem albo NavigationForm, bieżący przez current); notifications: ?View\Notifications\NotificationPanel; toasts: list<View\Toast> albo null = z sesji (Toast::SESSION_KEY); legalLinks: list<array{url: string, label: string}> (odnośniki prawne w stopce simplysmart); stylesheets: dodatkowe arkusze z public/assets/ po arkuszach Core; zoneStylesheets: arkusze strefy (ścieżki plików) na końcu; fontFaces: reguły @font-face strefy (podgląd strefy w design systemie, poza strefą); palettes: list<Enums\Palette> (kolorystyki strony, bez przełącznika w layoucie) i paletteScope: zakres zapamiętania wyboru; motyw i kolorystykę zmienia strona wyglądu w ustawieniach; icon: ikona nagłówka strony (x-admiro::app.context); sloty: actions (panel akcji w nagłówku), before (pasek nad aplikacją, np. podgląd w design systemie), brand (zamiast logo i stopki simplysmart). Menu rozwijane działają bez skryptu (<details>). Akcje konta (zwijanie, kontekst, ustawienia, powiadomienia) daje x-admiro::app.actions. variant: Enums\AppLayout — Sidebar (opisany wyżej) albo Workspace (.ss-shell--workspace): bez menu bocznego i top baru — navigation trafia tylko do mapy strony — a pod treścią przyklejona do dołu, lekko przezroczysta stopka .ss-workspace-footer: po lewej konto (awatar, imię i nazwisko) z menu nad stopką: pozycje konta (account.items), ustawienia (settingsUrl: ?string, adres ustawień, gdy settings jest null; inaczej settings->url), mapa aplikacji, profile społecznościowe (account.social: list<array{url, label, icon}>) i wylogowanie na dole, obok skróty strefy (shortcuts: list<NavigationItem> z Contracts\ZoneShortcuts — ikony-odnośniki z podpowiedzią, bieżący w kolorach odwróconych); globalna wyszukiwarka na środku w stylu okna czatu (x-admiro::app.search compose: „+” z akcjami search->actions, suwaki zakresów, przycisk szukania z rakietą; podpowiedzi otwierają się nad polem; na telefonie wyszukiwarka w pierwszym rzędzie stopki, pod nią konto i narzędzia); po prawej przełącznik kontekstu (x-admiro::app.context-switch, menu nad stopką), dzwonek powiadomień (panel z prawej krawędzi okna) i kalendarz (calendar: ?View\Calendar\CalendarMonth, x-admiro::app.calendar — prawy panel z miesiącem, podglądem dnia i miejscem na skrzynkę pocztową); mapa strony rozsuwana pod paskiem odnośnikiem do #ss-sitemap (NavigationItem::SITEMAP_ANCHOR, „Mapa aplikacji” w menu konta; stopka przestaje być przyklejona i rośnie w dół na końcu strony) — grupy stron w siatce komórek z cienkimi liniami — sitemap (np. Contracts\ZoneSitemap strefy), a bez niej navigation — z odnośnikami prawnymi (bez skryptu mapa jest widoczna). sitemap: ?list<View\Navigation\NavigationGroup>. --}}
@props(['variant' => \Simplysmart\Admiro\Enums\AppLayout::Sidebar, 'title', 'icon' => null, 'meta' => null, 'back' => null, 'navigation' => [], 'sitemap' => null, 'shortcuts' => [], 'calendar' => null, 'settings' => null, 'settingsUrl' => null, 'search' => null, 'account' => null, 'signOut' => null, 'contexts' => null, 'notifications' => null, 'toasts' => null, 'legalLinks' => [], 'description' => null, 'stylesheets' => [], 'zoneStylesheets' => [], 'palettes' => [], 'paletteScope' => '', 'fontFaces' => null])
@php($toasts ??= \Simplysmart\Admiro\View\Toast::fromSession(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY)))
@php($workspace = $variant === \Simplysmart\Admiro\Enums\AppLayout::Workspace)
<x-admiro::page :title="$title" :description="$description" robots="noindex, nofollow" :palettes="$palettes" :palette-scope="$paletteScope" :font-faces="$fontFaces">
    <x-slot:head>
        <x-admiro::styles :files="[...\Simplysmart\Admiro\View\CoreAssets::STYLESHEETS, ...$stylesheets]" />
        <x-admiro::zone-styles :paths="$zoneStylesheets" />
        <x-admiro::scripts :files="['js/app-shell.js', 'js/toasts.js', 'js/input-mask.js', 'js/pin-keypad.js', 'js/global-search.js', 'js/autosubmit.js', 'js/calendar-panel.js', 'js/tabs.js', 'js/live-search.js', 'js/tree-dnd.js', 'js/post-toggle.js']" />
    </x-slot:head>

    {{ $before ?? '' }}

    @php($hasFooter = ! isset($brand) && ! $workspace)
    <div class="ss-footer-host"@if ($hasFooter) data-ss-footer-root="ss-footer-host"@endif>
    <div @class(['ss-shell', 'ss-footer-stage', 'ss-shell--workspace' => $workspace]) data-ss-shell>
        <x-admiro::app.backdrop />
        @unless ($workspace)
        <aside class="ss-sidebar" id="ss-sidebar">
            <div class="ss-sidebar__header">
                @if ($account !== null)
                    @php($accountItems = $account['items'] ?? [])
                    @if ($accountItems !== [])
                        <details class="ss-account-menu ss-sidebar__account" data-ss-dropdown>
                            <summary class="ss-sidebar__identity" title="{{ $account['name'] }}">
                                <x-admiro::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                                <span class="ss-sidebar__identity-text">
                                    <span class="ss-sidebar__identity-name">{{ $account['name'] }}</span>
                                    @if (filled($account['subtitle'] ?? null))
                                        <span class="ss-sidebar__identity-subtitle">{{ $account['subtitle'] }}</span>
                                    @endif
                                </span>
                                <span class="ss-sr-only">{{ $account['label'] ?? text('account.menu') }}</span>
                            </summary>
                            <div class="ss-account-menu__panel ss-sidebar__panel">
                                <ul class="ss-account-menu__list">
                                    @foreach ($accountItems as $item)
                                        <li><a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="page" @endif><x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />{{ $item->label }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </details>
                    @else
                        <div class="ss-sidebar__identity" title="{{ $account['name'] }}">
                            <x-admiro::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                            <span class="ss-sidebar__identity-text">
                                <span class="ss-sidebar__identity-name">{{ $account['name'] }}</span>
                                @if (filled($account['subtitle'] ?? null))
                                    <span class="ss-sidebar__identity-subtitle">{{ $account['subtitle'] }}</span>
                                @endif
                            </span>
                        </div>
                    @endif
                @endif

                <div class="ss-sidebar__actions">
                    <x-admiro::app.actions :contexts="$contexts" :settings="$settings" :notifications="$notifications" />
                </div>
            </div>

            <nav class="ss-sidebar__nav" aria-label="{{ $settings?->active ? text('layout.settings') : text('layout.menu') }}">
                @if ($settings?->active)
                    <a class="ss-sidebar__link ss-sidebar__back" href="{{ $settings->backUrl }}" title="{{ text('layout.back_to_app') }}">
                        <x-admiro::icon name="arrow-left" class="ss-sidebar__icon" />
                        <span class="ss-sidebar__label">{{ text('layout.back_to_app') }}</span>
                    </a>
                    <p class="ss-sidebar__title">{{ text('layout.settings') }}</p>
                @endif
                @foreach ($navigation as $group)
                    <div class="ss-sidebar__section">
                        @if (filled($group->label))
                            <p class="ss-sidebar__group">{{ $group->label }}</p>
                        @endif
                        <ul class="ss-sidebar__list">
                            @foreach ($group->items as $item)
                                @if ($item->children !== [])
                                    <li class="ss-sidebar__branch">
                                        <a class="ss-sidebar__link ss-sidebar__branch-link" href="{{ $item->url }}" title="{{ $item->label }}" @if ($item->isExpanded()) aria-current="page" @endif>
                                            <x-admiro::icon :name="$item->icon" class="ss-sidebar__icon" />
                                            <span class="ss-sidebar__label">{{ $item->label }}</span>
                                        </a>
                                        <details class="ss-sidebar__tree" @if ($item->isExpanded()) open @endif>
                                            <summary @class(['ss-sidebar__link', 'ss-sidebar__summary', 'ss-sidebar__summary--current' => $item->current]) title="{{ $item->label }}">
                                                <x-admiro::icon :name="$item->icon" class="ss-sidebar__icon" />
                                                <span class="ss-sidebar__label">{{ $item->label }}</span>
                                                @if (filled($item->badge))
                                                    <span class="ss-sidebar__badge">{{ $item->badge }}</span>
                                                @endif
                                                <x-admiro::icon name="chevron-down" class="ss-sidebar__chevron" />
                                            </summary>
                                            <ul class="ss-sidebar__sublist">
                                                @foreach ($item->children as $child)
                                                    <li>
                                                        <a class="ss-sidebar__sublink" href="{{ $child->url }}" @if ($child->current) aria-current="page" @endif>
                                                            <span class="ss-sidebar__label">{{ $child->label }}</span>
                                                            @if (filled($child->badge))
                                                                <span class="ss-sidebar__badge">{{ $child->badge }}</span>
                                                            @endif
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </details>
                                    </li>
                                @else
                                    <li>
                                        <a class="ss-sidebar__link" href="{{ $item->url }}" title="{{ $item->label }}" @if ($item->current) aria-current="page" @endif>
                                            <x-admiro::icon :name="$item->icon" class="ss-sidebar__icon" />
                                            <span class="ss-sidebar__label">{{ $item->label }}</span>
                                            @if (filled($item->badge))
                                                <span class="ss-sidebar__badge">{{ $item->badge }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            @if ($signOut !== null)
                <form class="ss-sidebar__sign-out" method="POST" action="{{ $signOut }}">
                    @csrf
                    <button class="ss-sidebar__link" type="submit" title="{{ text('sign_in.sign_out') }}">
                        <x-admiro::icon name="log-out" class="ss-sidebar__icon" />
                        <span class="ss-sidebar__label">{{ text('sign_in.sign_out') }}</span>
                    </button>
                </form>
            @endif

            @isset($brand)
                <div class="ss-sidebar__brand">{{ $brand }}</div>
            @else
                <div class="ss-sidebar__brand">
                    <button class="ss-sidebar__brand-toggle" type="button" aria-expanded="false" aria-controls="ss-app-footer" title="{{ text('layout.footer_toggle') }}" data-ss-footer-toggle>
                        <x-admiro::logo class="ss-sidebar__logo" />
                        <span class="ss-sr-only">{{ text('layout.footer_toggle') }}</span>
                    </button>
                </div>
            @endisset
        </aside>

        <div class="ss-shell__backdrop" data-ss-sidebar-backdrop hidden></div>
        @endunless

        <div class="ss-shell__main">
            @unless ($workspace)
                <header class="ss-topbar">
                    <button class="ss-icon-button ss-topbar__menu" type="button" aria-controls="ss-sidebar" aria-expanded="false" data-ss-sidebar-toggle>
                        <x-admiro::icon name="sidebar" class="ss-icon-button__icon" />
                        <span class="ss-sr-only">{{ text('layout.toggle_menu') }}</span>
                    </button>

                    @if ($search !== null)
                        <x-admiro::app.search :search="$search" />
                    @endif
                </header>
            @endunless

            <main class="ss-main">
                <x-admiro::app.context :title="$title" :meta="$meta" :back="$back" :icon="$icon">
                    @isset($actions)
                        <x-slot:actions :class="$actions->attributes->get('class')">{{ $actions }}</x-slot:actions>
                    @endisset
                </x-admiro::app.context>

                {{ $slot }}
            </main>

            @if ($workspace)
                <footer class="ss-workspace-footer">
                    <div class="ss-workspace-footer__bar">
                        @if ($account !== null)
                            <details class="ss-account-menu ss-workspace-footer__account" data-ss-dropdown>
                                <summary class="ss-workspace-footer__account-toggle">
                                    <x-admiro::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                                    <span class="ss-workspace-footer__account-name">{{ $account['name'] }}</span>
                                    <x-admiro::icon name="chevron-down" class="ss-workspace-footer__account-chevron" />
                                    <span class="ss-sr-only">{{ $account['label'] ?? text('account.menu') }}</span>
                                </summary>
                                <div class="ss-account-menu__panel ss-workspace-footer__account-panel">
                                    <ul class="ss-account-menu__list">
                                        @foreach ($account['items'] ?? [] as $item)
                                            <li><a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="page" @endif><x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />{{ $item->label }}</a></li>
                                        @endforeach
                                        @if (($settingsUrl ?? $settings?->url) !== null)
                                            <li><a class="ss-account-menu__link" href="{{ $settingsUrl ?? $settings?->url }}"><x-admiro::icon name="settings" class="ss-account-menu__icon" />{{ text('layout.settings') }}</a></li>
                                        @endif
                                        <li><a class="ss-account-menu__link" href="#{{ \Simplysmart\Admiro\View\Navigation\NavigationItem::SITEMAP_ANCHOR }}"><x-admiro::icon name="sitemap" class="ss-account-menu__icon" />{{ text('layout.sitemap_open') }}</a></li>
                                        @foreach ($account['social'] ?? [] as $link)
                                            <li><a class="ss-account-menu__link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"><x-admiro::icon :name="$link['icon']" class="ss-account-menu__icon" />{{ $link['label'] }}</a></li>
                                        @endforeach
                                    </ul>
                                    @if ($signOut !== null)
                                        <form class="ss-workspace-footer__sign-out" method="POST" action="{{ $signOut }}">
                                            @csrf
                                            <button class="ss-account-menu__link" type="submit"><x-admiro::icon name="log-out" class="ss-account-menu__icon" />{{ text('sign_in.sign_out') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </details>
                        @endif
                        @if ($shortcuts !== [])
                            <ul class="ss-workspace-footer__shortcuts" aria-label="{{ text('layout.shortcuts') }}">
                                @foreach ($shortcuts as $shortcut)
                                    <li><a class="ss-icon-button ss-workspace-footer__tool" href="{{ $shortcut->url }}" title="{{ $shortcut->label }}" @if ($shortcut->current) aria-current="page" @endif><x-admiro::icon :name="$shortcut->icon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ $shortcut->label }}</span></a></li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="ss-workspace-footer__search">
                            @if ($search !== null)
                                <x-admiro::app.search :search="$search" compose />
                            @endif
                        </div>
                        <div class="ss-workspace-footer__tools">
                            @if ($contexts !== null && $contexts !== [])
                                <x-admiro::app.context-switch :contexts="$contexts" toggle-class="ss-icon-button ss-workspace-footer__tool" />
                            @endif
                            @if ($notifications !== null)
                                <x-admiro::app.notifications :notifications="$notifications" right toggle-class="ss-icon-button ss-workspace-footer__tool" />
                            @endif
                            @if ($calendar !== null)
                                <x-admiro::app.calendar :calendar="$calendar" />
                            @endif
                        </div>
                    </div>
                    <nav class="ss-workspace-footer__sitemap" id="{{ \Simplysmart\Admiro\View\Navigation\NavigationItem::SITEMAP_ANCHOR }}" aria-label="{{ text('layout.sitemap') }}" data-ss-sitemap>
                        @foreach ($sitemap ?? $navigation as $group)
                            <section class="ss-workspace-footer__column">
                                <h2 class="ss-workspace-footer__heading">{{ filled($group->label) ? $group->label : text('layout.sitemap_start') }}</h2>
                                <ul class="ss-workspace-footer__links">
                                    @foreach ($group->items as $item)
                                        @if ($item instanceof \Simplysmart\Admiro\View\Navigation\NavigationItem)
                                            <li><a class="ss-link ss-workspace-footer__link" href="{{ $item->url }}" @if ($item->current) aria-current="page" @endif><x-admiro::icon :name="$item->icon" class="ss-workspace-footer__link-icon" />{{ $item->label }}</a></li>
                                            @foreach ($item->children as $child)
                                                <li class="ss-workspace-footer__child"><a class="ss-link" href="{{ $child->url }}" @if ($child->current) aria-current="page" @endif>{{ $child->label }}</a></li>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                        @if ($legalLinks !== [])
                            <section class="ss-workspace-footer__column">
                                <h2 class="ss-workspace-footer__heading">{{ text('layout.legal') }}</h2>
                                <ul class="ss-workspace-footer__links">
                                    @foreach ($legalLinks as $link)
                                        <li><a class="ss-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    </nav>
                </footer>
            @endif
        </div>
    </div>
    @if ($hasFooter)
        <x-admiro::brand-footer id="ss-app-footer" :legal-links="$legalLinks" media="(min-width: 64rem)" />
    @endif
    </div>

    <x-admiro::toasts :toasts="$toasts" />
</x-admiro::page>
