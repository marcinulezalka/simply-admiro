{{-- Karta „Zobacz także” stron dokumentów (slot related x-mainboard::content.layout): pozostałe opublikowane dokumenty i polityka cookies jako odnośniki .ss-related z ikoną, tytułem i opisem. $related: list<array{url: string, title: string, text: string, icon: string}> (Legal\LegalLinks::related()). --}}
@foreach ($related as $link)
    <a class="ss-related" href="{{ $link['url'] }}">
        <x-mainboard::icon :name="$link['icon']" class="ss-icon--tile" />
        <span class="ss-related__body">
            <span class="ss-related__title">{{ $link['title'] }}</span>
            <span class="ss-related__text">{{ $link['text'] }}</span>
        </span>
        <x-mainboard::icon name="arrow-right" class="ss-related__arrow" />
    </a>
@endforeach
