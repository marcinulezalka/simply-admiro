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

namespace Simplysmart\Admiro\Panel\Search;

use Illuminate\Support\Str;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Auth\AccountColumns;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Enums\SearchScope;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Http\AdmiroNavigation;
use Simplysmart\Admiro\Queries\Accounts\ZoneAccounts;
use Simplysmart\Admiro\Queries\Dictionaries\DictionarySearch;
use Simplysmart\Admiro\Queries\Organizations\OrganizationList;
use Simplysmart\Admiro\View\Navigation\NavigationItem;
use Simplysmart\Admiro\View\Navigation\SearchResult;
use Simplysmart\Admiro\View\Navigation\SearchResultGroup;

/**
 * Wyniki globalnej wyszukiwarki strefy administratorów w zaznaczonych zakresach (SearchScope), tylko w zakresach
 * dozwolonych administratorowi: języki (także po krajach, np. „polska” → polski; z flagami krajów, do kreatora
 * języków z tym samym tekstem, a bez `settings.manage` do słownika), kraje i waluty (słowniki), organizacje (do kreatora
 * organizacji z tym samym tekstem — lista wyników pod wyszukiwarką; z przełącznikiem włączenia przy `organizations.manage`,
 * a organizacja operatora z plakietką „Domyślna”) i administratorzy (imię i nazwisko albo dokładny e-mail), strony panelu (pozycje menu i ustawień po nazwie,
 * bez polskich znaków i wielkości liter). Każda grupa ma najwyżej `$limit` wyników i odnośnik do reszty.
 */
final readonly class AdmiroSearch
{
    public const int SUGGESTIONS = 5;

    public const int PAGE = 50;

    public function __construct(
        private AdminContext $context,
        private DictionarySearch $dictionaries,
        private OrganizationList $organizations,
        private ZoneAccounts $accounts,
        private AdmiroNavigation $navigation,
    ) {}

    /**
     * Zakresy z żądania (wartości `in[]`) w granicach dozwolonych; bez wyboru — wszystkie dozwolone.
     *
     * @param array<array-key, mixed>|null $requested
     *
     * @return list<SearchScope>
     */
    public function scopes(?array $requested): array
    {
        $available = SearchScope::available($this->context);

        if ($requested === null) {
            return $available;
        }

        return array_values(array_filter($available, static fn(SearchScope $scope): bool => in_array($scope->value, $requested, true)));
    }

    /**
     * @param list<SearchScope> $scopes
     *
     * @return list<SearchResultGroup>
     */
    public function groups(string $term, array $scopes, int $limit): array
    {
        if (trim($term) === '') {
            return [];
        }

        $groups = [];

        foreach ($scopes as $scope) {
            $groups = [...$groups, ...match ($scope) {
                SearchScope::Languages => [$this->languages($term, $limit)],
                SearchScope::Dictionaries => [$this->countries($term, $limit), $this->currencies($term, $limit)],
                SearchScope::Directory => [$this->organizationResults($term, $limit), $this->administrators($term, $limit)],
                SearchScope::Pages => [$this->pages($term, $limit)],
            }];
        }

        return array_values(array_filter($groups, static fn(?SearchResultGroup $group): bool => $group !== null && $group->results !== []));
    }

    private function languages(string $term, int $limit): SearchResultGroup
    {
        $found = $this->dictionaries->languages($term, $limit + 1);
        $inWizard = $this->context->can(OnboardingStep::Languages->permission());
        $list = $inWizard
            ? $this->route('onboarding.show', ['step' => OnboardingStep::Languages->value, 'q' => $term])
            : $this->route('dictionaries.index', ['dictionary' => 'languages']);
        $results = [];

        foreach ($found->take($limit) as $language) {
            $results[] = new SearchResult(
                $language->translatedName(),
                $list . '#language-' . $language->code,
                implode(' · ', array_filter([$language->native_name, $language->code, $language->is_active ? text('admiro::admin.state_active') : null])),
                flags: $language->flagIcons(),
            );
        }

        return new SearchResultGroup(text('admiro::search.groups.languages'), $results, $found->count() > $limit ? $list : null);
    }

    private function countries(string $term, int $limit): SearchResultGroup
    {
        $found = $this->dictionaries->countries($term, $limit + 1);
        $list = $this->route('dictionaries.index', ['dictionary' => 'countries']);
        $results = array_values($found->take($limit)->map(static fn(Country $country): SearchResult => new SearchResult(
            $country->translatedName(),
            $list . '#entry-' . $country->alpha_code_2,
            implode(' · ', array_filter([$country->alpha_code_2, $country->alpha_code_3])),
            flags: $country->flag_icon !== null ? [$country->flag_icon] : [],
        ))->all());

        return new SearchResultGroup(text('admiro::search.groups.countries'), $results, $found->count() > $limit ? $list : null);
    }

    private function currencies(string $term, int $limit): SearchResultGroup
    {
        $found = $this->dictionaries->currencies($term, $limit + 1);
        $list = $this->route('dictionaries.index', ['dictionary' => 'currencies']);
        $results = array_values($found->take($limit)->map(static fn(Currency $currency): SearchResult => new SearchResult(
            $currency->translatedName(),
            $list . '#entry-' . $currency->alpha_code_3,
            $currency->alpha_code_3,
            'credit-card',
        ))->all());

        return new SearchResultGroup(text('admiro::search.groups.currencies'), $results, $found->count() > $limit ? $list : null);
    }

    private function organizationResults(string $term, int $limit): ?SearchResultGroup
    {
        if (! $this->context->can(Permission::OrganizationsView)) {
            return null;
        }

        $found = $this->organizations->search($term, $limit + 1);
        $canEdit = $this->context->can(Permission::OrganizationsManage);
        $inWizard = $this->context->can(OnboardingStep::Organizations->permission());
        $list = $inWizard
            ? $this->route('onboarding.show', ['step' => OnboardingStep::Organizations->value, 'q' => $term])
            : $this->route('organizations.index');
        $results = array_values($found->take($limit)->map(function (Organization $organization) use ($canEdit, $inWizard, $list): SearchResult {
            $operator = $organization->id === Organization::OPERATOR_ID;

            return new SearchResult(
                $organization->name,
                match (true) {
                    $inWizard => $list . '#organization-' . $organization->token,
                    $canEdit => $this->route('organizations.edit', ['organization' => $organization->token]),
                    default => $list,
                },
                implode(' · ', array_filter([
                    $organization->nip !== null ? text('admiro::search.nip', ['nip' => $organization->nip]) : null,
                    $organization->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive'),
                ])),
                'building',
                toggleUrl: $canEdit && ! $operator ? $this->route('organizations.toggle', ['organization' => $organization->token]) : null,
                active: $organization->is_active,
                badge: $operator ? text('admiro::dashboard.onboarding_organization_default') : null,
            );
        })->all());

        return new SearchResultGroup(text('admiro::search.groups.organizations'), $results, $found->count() > $limit ? $list : null);
    }

    private function administrators(string $term, int $limit): ?SearchResultGroup
    {
        if (! $this->context->can(Permission::UsersManage)) {
            return null;
        }

        $found = $this->accounts->search($this->context->zone(), $term, $limit + 1);
        $list = $this->route('administrators.index');
        $results = [];

        foreach (array_slice($found, 0, $limit) as $account) {
            $name = $account->getAttribute(AccountColumns::NAME);
            $results[] = new SearchResult(is_string($name) ? $name : '', $list, null, 'shield-check');
        }

        return new SearchResultGroup(text('admiro::search.groups.administrators'), $results, count($found) > $limit ? $list : null);
    }

    private function pages(string $term, int $limit): SearchResultGroup
    {
        $needle = self::normalized($term);
        $results = [];

        foreach ($this->navigation->sitemap() as $group) {
            foreach ($group->items as $item) {
                if (! $item instanceof NavigationItem) {
                    continue;
                }

                foreach ([$item, ...$item->children] as $page) {
                    if (! str_starts_with($page->url, '#') && str_contains(self::normalized($page->label), $needle)) {
                        $results[$page->url] = new SearchResult($page->label, $page->url, $group->label, $page->icon !== '' ? $page->icon : $item->icon);
                    }
                }
            }
        }

        return new SearchResultGroup(text('admiro::search.groups.pages'), array_slice(array_values($results), 0, $limit));
    }

    /**
     * @param array<string, string> $parameters
     */
    private function route(string $name, array $parameters = []): string
    {
        return route($this->context->zone()->routeName($name), $parameters);
    }

    private static function normalized(string $text): string
    {
        return Str::lower(Str::ascii($text));
    }
}
