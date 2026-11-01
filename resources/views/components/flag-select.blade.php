{{-- Lista wyboru kraju z flagą jako ikoną w polu (np. kraj adresu i prefiks telefonu w x-admiro::organization.fields): obrazek flagi wybranej opcji po lewej w polu jak ikona x-admiro::input (.ss-input-wrap--icon), plik SVG z trasy Core `admiro.flags.show` (Http\Controllers\FlagController, długi cache). Strona nie osadza flag wszystkich opcji: niesie tylko mapę wartość → kod flagi (data-ss-flags) i szablon adresu (data-ss-flag-url z kodem FLAG_PLACEHOLDER podanym w data-ss-flag-placeholder), a js/flag-select.js po zmianie wyboru ustawia adres obrazka albo go chowa, gdy opcja nie ma flagi. Bez skryptu zostaje flaga opcji wybranej przy otwarciu strony. Opcje <option> w slocie. flags: array<string, ?string> (wartość opcji → kod flagi `flag_icon`; bez kodu brak flagi); selected: ?string (wartość wybrana przy otwarciu); reszta atrybutów trafia na <select> (id, name, aria-label…). --}}
@props(['flags', 'selected' => null])
@php
    $codes = array_filter($flags, static fn (?string $flag): bool => filled($flag));
    $current = $codes[(string) $selected] ?? null;
@endphp
<div class="ss-input-wrap ss-input-wrap--icon ss-flag-select" data-ss-flag-select data-ss-flag-url="{{ route('admiro.flags.show', ['flag' => \Simplysmart\Admiro\Http\Controllers\FlagController::FLAG_PLACEHOLDER]) }}" data-ss-flags="{{ json_encode($codes) }}" data-ss-flag-placeholder="{{ \Simplysmart\Admiro\Http\Controllers\FlagController::FLAG_PLACEHOLDER }}">
    <img class="ss-input-wrap__icon ss-flag ss-flag-select__flag" @if ($current !== null) src="{{ route('admiro.flags.show', ['flag' => $current]) }}" @else hidden @endif alt="" width="20" height="15" data-ss-flag-image>
    <select {{ $attributes->merge(['class' => 'ss-input']) }}>
        {{ $slot }}
    </select>
</div>
