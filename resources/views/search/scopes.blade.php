{{-- Menu zakresów globalnej wyszukiwarki (x-admiro::app.search): przycisk z ikoną suwaków otwiera listę zakresów jako przełączniki z ikonami (pola `<scopeName>[]`; puste pole ukryte, więc wyłączenie wszystkich też dociera do serwera). $search: View\Navigation\SearchBox. --}}
<details class="ss-search__menu ss-search__scopes" data-ss-dropdown>
    <summary class="ss-search__menu-toggle ss-search__scopes-toggle" title="{{ text('layout.search_scopes') }}">
        <x-admiro::icon name="settings" class="ss-search__menu-icon ss-search__scopes-icon" />
        <span class="ss-sr-only">{{ text('layout.search_scopes') }}</span>
    </summary>
    <fieldset class="ss-search__scopes-panel">
        <legend class="ss-search__scopes-title">{{ text('layout.search_scopes') }}</legend>
        <input type="hidden" name="{{ $search->scopeName }}[]" value="">
        @foreach ($search->scopes as $scope)
            <x-admiro::switch class="ss-search__scope" :id="'ss-search-scope-' . $scope->value" :label="$scope->label" :icon="$scope->icon" :checked="$scope->checked" :name="$search->scopeName . '[]'" :value="$scope->value" data-ss-search-scope />
        @endforeach
    </fieldset>
</details>
