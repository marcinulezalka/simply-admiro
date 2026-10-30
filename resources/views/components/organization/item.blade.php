{{-- Jednostka konta jako wiersz listy (np. menu konta w layoucie stref): biała karta z miękkim cieniem — po lewej kafelek z ikoną rodzaju (organizacja, jednostka — budynek; departament — siatka) w kolorze akcentu, obok nazwa skrócona (departament z jednostką, Organization::contextName()) i pod nią rodzaj z NIP-em krojem mono; bieżąca jednostka z obwódką akcentu i aria-current. organization: Models\Organization; kind: string (nazwa rodzaju); icon: string; current: bool; href: ?string (wiersz jako odnośnik). --}}
@props(['organization', 'kind', 'icon' => 'building', 'current' => false, 'href' => null])
@php($tag = $href !== null ? 'a' : 'div')
<{{ $tag }} {{ $attributes->class(['ss-org-item', 'ss-org-item--current' => $current]) }} @if ($href !== null) href="{{ $href }}" @endif @if ($current) aria-current="true" @endif>
    <span class="ss-org-item__tile"><x-admiro::icon :name="$icon" class="ss-org-item__icon" /></span>
    <span class="ss-org-item__text">
        <span class="ss-org-item__name">{{ $organization->contextName(short: true) }}</span>
        <span class="ss-org-item__sub">{{ $kind }} · {{ text_data($organization, \Simplysmart\Admiro\Models\Organization::NIP) }} {{ $organization->nip ?: '—' }}</span>
    </span>
</{{ $tag }}>
