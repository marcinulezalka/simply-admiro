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

namespace Simplysmart\Admiro\Panel\Http;

use Illuminate\Http\Request;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Http\Controllers\OnboardingController;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Enums\SearchScope;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Contracts\ZoneNavigation;
use Simplysmart\Admiro\Contracts\ZoneNotifications;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Contracts\ZoneShortcuts;
use Simplysmart\Admiro\Contracts\ZoneSitemap;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Models\AuditEvent;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Queries\Audit\AuditLog;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\View\Navigation\NavigationGroup;
use Simplysmart\Admiro\View\Navigation\NavigationItem;
use Simplysmart\Admiro\View\Navigation\SearchBox;
use Simplysmart\Admiro\View\Navigation\SearchScope as SearchScopeOption;
use Simplysmart\Admiro\View\AuditEventLabel;
use Simplysmart\Admiro\View\Notifications\NotificationItem;
use Simplysmart\Admiro\View\Notifications\NotificationPanel;

/**
 * Menu strefy administratorów w layoucie aplikacji: w menu bocznym pulpit i kreator onboardingu, w mapie strony
 * (ZoneSitemap, stopka layoutu Workspace) także grupy stref i kont, organizacji i systemu (dziennik zdarzeń) oraz
 * ustawień; w ustawieniach grupy aplikacji (ustawienia, polityka cookies, polityka prywatności, RODO) i słowników (słowniki, kursy walut, dni wolne)
 * — każda pozycja tylko z uprawnieniem administratora (puste grupy znikają); pod pulpitem kreator „Onboarding”
 * (`settings.manage`); druga linia pod nazwą konta: role administratora w organizacji operatora. Globalna
 * wyszukiwarka na trasę `search` z podpowiedziami (`search.suggest`), zakresami SearchScope dozwolonymi
 * administratorowi (zaznaczone z `in[]` żądania, bez nich wszystkie; z ikonami) i akcjami dodawania pod „+”
 * (organizacja, właściciel strefy, kurs waluty, dzień wolny, polityka cookies — każda z uprawnieniem). Panel
 * powiadomień (ZoneNotifications): z `audit.view` zakładka dziennika z ostatnimi wpisami (AUDIT_ITEMS) i odnośnikiem do
 * dziennika. Skróty w stopce (ZoneShortcuts): dashboard, ulubione i pulpit roboczy.
 */
final readonly class AdmiroNavigation implements ZoneNavigation, ZoneSitemap, ZoneShortcuts, ZoneNotifications
{
    /** Liczba ostatnich wpisów dziennika w panelu powiadomień. */
    public const int AUDIT_ITEMS = 5;

    public function __construct(
        private AdminContext $context,
        private Request $request,
        private ZoneRoleNames $roles,
        private AuditLog $audit,
        private ZoneRegistry $zones,
        private DefaultEntries $defaults,
        private CurrentRole $role,
    ) {}

    /**
     * Menu boczne: pulpit i kreator onboardingu; pozostałe strony są na razie w mapie strony (sitemap()) i trafiają
     * do menu stopniowo.
     */
    public function groups(): array
    {
        return $this->nonEmpty([$this->startGroup()]);
    }

    /**
     * Mapa strony w stopce layoutu (i strony panelu w globalnej wyszukiwarce): pulpit i onboarding, strefy i konta,
     * organizacje, system oraz grupy ustawień; Core dokłada po nich grupę konta.
     */
    public function sitemap(): array
    {
        return $this->nonEmpty([
            $this->startGroup(),
            new NavigationGroup(text('admiro::navigation.groups.zones'), $this->items(
                $this->itemIf(Permission::ZonesView, 'zones.index', 'admiro::admin.zones_title', 'zones', 'zones.*'),
                $this->itemIf(Permission::OwnersAssign, 'owners.create', 'admiro::admin.owner_title', 'user-plus', 'owners.*'),
                $this->itemIf(Permission::UsersManage, 'administrators.index', 'admiro::admin.administrators_title', 'shield-check', 'administrators.*'),
            )),
            new NavigationGroup(text('admiro::navigation.groups.organizations'), $this->items(
                $this->itemIf(Permission::OrganizationsView, 'organizations.index', 'admiro::admin.organizations_title', 'building', 'organizations.*'),
            )),
            ...$this->settings(),
        ]);
    }

    public function settings(): array
    {
        return $this->nonEmpty([
            new NavigationGroup(text('admiro::navigation.groups.application'), $this->items(
                $this->itemIf(Permission::SettingsView, 'settings.show', 'admiro::admin.settings_title', 'settings', 'settings.*'),
                ...array_map($this->legalDocumentItem(...), LegalDocumentType::cases()),
            )),
            new NavigationGroup(text('admiro::navigation.groups.dictionaries'), $this->items(
                $this->itemIf(Permission::SettingsView, 'dictionaries.index', 'admiro::admin.dictionaries_title', 'dictionaries', 'dictionaries.*', ['dictionary' => 'countries']),
                $this->itemIf(Permission::SettingsView, 'exchange-rates.index', 'admiro::admin.exchange_rates_title', 'credit-card', 'exchange-rates.*'),
                $this->itemIf(Permission::SettingsView, 'holidays.index', 'admiro::admin.holidays_title', 'calendar', 'holidays.*'),
            )),
        ]);
    }

    public function search(): SearchBox
    {
        $zone = $this->context->zone();
        $requested = $this->request->has('in') ? $this->request->input('in') : null;
        $scopes = array_map(
            static fn(SearchScope $scope): SearchScopeOption => new SearchScopeOption($scope->value, $scope->label(), ! is_array($requested) || in_array($scope->value, $requested, true), $scope->icon()),
            SearchScope::available($this->context),
        );

        return new SearchBox(
            route($zone->routeName('search')),
            text('admiro::search.placeholder'),
            value: $this->request->string('q')->toString(),
            scopes: $scopes,
            suggestUrl: route($zone->routeName('search.suggest')),
            actions: [...$this->organizationActions(), ...$this->items(
                $this->itemIf(Permission::SettingsManage, 'exchange-rates.index', 'admiro::search.actions.exchange_rate', 'credit-card', 'exchange-rates.index'),
                $this->itemIf(Permission::SettingsManage, 'holidays.index', 'admiro::search.actions.holiday', 'calendar', 'holidays.index'),
                $this->itemIf(Permission::SettingsManage, 'legal-documents.index', 'admiro::search.actions.cookie_policy', LegalDocumentType::Cookies->icon(), 'legal-documents.index', ['document' => LegalDocumentType::Cookies->value]),
            )],
        );
    }

    /**
     * Akcje „+” zakładające organizację: nowa organizacja i właściciel strefy w organizacji, tylko z uprawnieniami
     * (globalna wyszukiwarka i krok „Organizacje” kreatora onboardingu).
     *
     * @return list<NavigationItem>
     */
    public function organizationActions(): array
    {
        return $this->items(
            $this->itemIf(Permission::OrganizationsManage, 'organizations.create', 'admiro::search.actions.organization', 'building', 'organizations.create'),
            $this->itemIf(Permission::OwnersAssign, 'owners.create', 'admiro::search.actions.owner', 'user-plus', 'owners.create'),
        );
    }

    /**
     * Menu „+” wyszukiwarki zadania kreatora onboardingu: akcje dodawania dopasowane do zadania (tylko dozwolone).
     *
     * @return list<NavigationItem>
     */
    public function wizardActions(OnboardingStep $step): array
    {
        $country = $this->defaults->country()?->alpha_code_2;

        return match ($step) {
            OnboardingStep::Organizations => $this->organizationActions(),
            OnboardingStep::Languages => $this->items(
                $this->itemIf(Permission::SettingsManage, 'onboarding.show', 'admiro::search.actions.language', 'languages', 'dictionaries.*', ['step' => OnboardingStep::Languages->value, 'view' => OnboardingController::HIDDEN_LANGUAGES]),
                $this->itemIf(Permission::SettingsManage, 'settings.show', 'admiro::search.actions.regional', 'locale', 'settings.*'),
            ),
            OnboardingStep::Countries => $this->items(
                $this->itemIf(Permission::SettingsManage, 'dictionaries.index', 'admiro::search.actions.country', 'flag', 'dictionaries.*', ['dictionary' => 'countries']),
                $this->itemIf(Permission::SettingsManage, 'settings.show', 'admiro::search.actions.regional', 'locale', 'settings.*'),
            ),
            OnboardingStep::Currencies => $this->items(
                $this->itemIf(Permission::SettingsManage, 'exchange-rates.index', 'admiro::search.actions.exchange_rate', 'credit-card', 'exchange-rates.*'),
                $this->itemIf(Permission::SettingsManage, 'dictionaries.index', 'admiro::search.actions.currency', 'dictionaries', 'dictionaries.*', ['dictionary' => 'currencies']),
            ),
            OnboardingStep::Calendar => $this->items(
                $this->itemIf(Permission::SettingsManage, 'holidays.index', 'admiro::search.actions.holiday', 'calendar', 'holidays.*'),
            ),
            OnboardingStep::Taxes => $country === null ? [] : $this->items(
                $this->itemIf(Permission::SettingsManage, 'tax-rates.index', 'admiro::search.actions.tax_rate', 'invoice', 'tax-rates.*', ['country' => $country]),
            ),
            OnboardingStep::Administrators => $this->items(
                $this->itemIf(Permission::OwnersAssign, 'owners.create', 'admiro::search.actions.owner', 'user-plus', 'owners.create'),
            ),
            default => [],
        };
    }

    public function notifications(): NotificationPanel
    {
        if (! $this->context->can(Permission::AuditView)) {
            return new NotificationPanel([]);
        }

        $url = route($this->context->zone()->routeName('audit.index'));
        $entries = $this->audit->page(null, null, null, null, self::AUDIT_ITEMS)->items();

        return new NotificationPanel([], auditItems: array_values(array_map(fn(AuditEvent $entry): NotificationItem => new NotificationItem(
            AuditEventLabel::of($entry->event, $entry->zone !== null && $this->zones->has($entry->zone) ? $this->zones->get($entry->zone) : null),
            local_date($entry->occurred_at) . ' ' . $entry->occurred_at->format('H:i'),
            $entry->zone,
            $url,
            icon: 'history',
        ), $entries)), auditUrl: $url);
    }

    /**
     * Skróty w stopce Workspace: dashboard (dom), ulubione (serce) i pulpit roboczy — dla każdego administratora.
     *
     * @return list<NavigationItem>
     */
    public function shortcuts(): array
    {
        return [
            $this->item('dashboard', 'admiro::navigation.status', 'home'),
            $this->item('favorites', 'admiro::navigation.favorites', 'heart'),
            $this->item('board', 'admiro::navigation.board', 'board'),
        ];
    }

    public function subtitle(): ?string
    {
        $active = $this->role->of($this->context->admin(), Organization::OPERATOR_ID);
        $roles = array_map($this->context->zone()->roleLabel(...), $active !== null ? [$active] : $this->roles->of($this->context->admin(), Organization::OPERATOR_ID));

        return $roles === [] ? null : implode(' · ', $roles);
    }

    /**
     * Grupa „System”: status aplikacji (pulpit), ulubione, pulpit roboczy, onboarding, mapa aplikacji (rozsuwa mapę strony w stopce, NavigationItem::SITEMAP_ANCHOR), wiki (słownik pojęć),
     * dziennik zdarzeń i ustawienia (na stronach ustawień menu boczne przełącza się na menu ustawień z powrotem).
     */
    private function startGroup(): NavigationGroup
    {
        return new NavigationGroup(text('admiro::navigation.groups.system'), $this->items(
            $this->item('dashboard', 'admiro::navigation.status', 'home'),
            $this->item('favorites', 'admiro::navigation.favorites', 'heart'),
            $this->item('board', 'admiro::navigation.board', 'board'),
            $this->itemIf(Permission::SettingsManage, 'onboarding.index', 'admiro::dashboard.onboarding_title', 'rocket', 'onboarding.*'),
            new NavigationItem(text('admiro::navigation.sitemap'), '#' . NavigationItem::SITEMAP_ANCHOR, 'sitemap'),
            $this->item('wiki', 'admiro::navigation.wiki', 'help-circle'),
            $this->itemIf(Permission::AuditView, 'audit.index', 'admiro::admin.audit_title', 'history', 'audit.*'),
            $this->itemIf(Permission::SettingsView, 'settings.show', 'admiro::navigation.system_settings', 'settings', 'settings.*'),
        ));
    }

    /**
     * Grupy z pozycjami (grupy bez pozycji, do których administrator nie ma uprawnień, znikają).
     *
     * @param list<NavigationGroup> $groups
     *
     * @return list<NavigationGroup>
     */
    private function nonEmpty(array $groups): array
    {
        return array_values(array_filter($groups, static fn(NavigationGroup $group): bool => $group->items !== []));
    }

    /**
     * @return list<NavigationItem>
     */
    private function items(?NavigationItem ...$items): array
    {
        return array_values(array_filter($items));
    }

    /**
     * Dokument prawny Core w ustawieniach (`settings.view`), aktywny tylko na stronie tego dokumentu.
     */
    private function legalDocumentItem(LegalDocumentType $document): ?NavigationItem
    {
        if (! $this->context->can(Permission::SettingsView)) {
            return null;
        }

        $zone = $this->context->zone();
        $active = $this->request->routeIs($zone->routeName('legal-documents.*'))
            && in_array($this->request->route('document'), [$document, $document->value], true);

        return new NavigationItem($document->label(), route($zone->routeName('legal-documents.index'), ['document' => $document->value]), $document->icon(), $active);
    }

    /**
     * @param array<string, string> $parameters
     */
    private function itemIf(Permission $permission, string $route, string $label, string $icon, string $active, array $parameters = []): ?NavigationItem
    {
        return $this->context->can($permission) ? $this->item($route, $label, $icon, $active, $parameters) : null;
    }

    /**
     * @param array<string, string> $parameters
     */
    private function item(string $route, string $label, string $icon, ?string $active = null, array $parameters = []): NavigationItem
    {
        $zone = $this->context->zone();

        return new NavigationItem(text($label), route($zone->routeName($route), $parameters), $icon, $this->request->routeIs($zone->routeName($active ?? $route)));
    }
}
