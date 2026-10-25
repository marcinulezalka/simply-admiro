{{-- Zakładki słowników lokalizacji (kraje, języki, waluty) nad ustawieniami i słownikami; bieżący z aria-current. $current: ?Dictionary. --}}
<nav aria-label="{{ text('admiro::admin.dictionaries_title') }}">
    <ul class="ss-tabs">
        <li><a class="ss-tabs__link" href="{{ route('admiro.settings.show') }}" @if ($current === null) aria-current="page" @endif><x-mainboard::icon name="settings" class="ss-tabs__icon" />{{ text('admiro::admin.settings_title') }}</a></li>
        @foreach (\Simplysmart\Mainboard\Localization\Dictionary::cases() as $dictionary)
            <li><a class="ss-tabs__link" href="{{ route('admiro.dictionaries.index', ['dictionary' => $dictionary->value]) }}" @if ($dictionary === $current) aria-current="page" @endif>{{ text('admiro::admin.dictionary.' . $dictionary->value) }}</a></li>
        @endforeach
    </ul>
</nav>
