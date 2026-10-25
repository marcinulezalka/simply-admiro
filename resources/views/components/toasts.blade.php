{{-- Toasty (alerty aplikacji) na dole ekranu: kolor stanu, ikona i nazwa typu (Enums\ToastType), treść, opcjonalna akcja, zamknięcie. Błąd ogłaszany jako alert i zostaje do zamknięcia; pozostałe znikają same (js/toasts.js, pauza przy najechaniu i fokusie). toasts: list<View\Toast>; inline: toasty w treści strony (próbki), bez nakładki i bez znikania. --}}
@props(['toasts' => [], 'inline' => false])
@if ($toasts !== [])
    <div @class(['ss-toasts', 'ss-toasts--inline' => $inline]) aria-label="{{ text('layout.toasts') }}" data-ss-toasts>
        @foreach ($toasts as $toast)
            <div class="ss-toast ss-toast--{{ $toast->type->value }}" role="{{ $toast->type->role() }}" data-ss-toast @if (! $inline && $toast->type->dismissesItself()) data-ss-toast-auto @endif>
                <x-admiro::icon :name="$toast->type->icon()" class="ss-toast__icon" />
                <div class="ss-toast__body">
                    <p class="ss-toast__title">@if ($toast->title !== null)<span class="ss-sr-only">{{ $toast->type->label() }}: </span>@endif{{ $toast->heading() }}</p>
                    <p class="ss-toast__message">{{ $toast->message }}</p>
                    @if ($toast->hasAction())
                        <a class="ss-toast__action ss-link" href="{{ $toast->actionUrl }}">{{ $toast->actionLabel }}</a>
                    @endif
                </div>
                <button class="ss-toast__close" type="button" data-ss-toast-close>
                    <x-admiro::icon name="close" class="ss-toast__close-icon" />
                    <span class="ss-sr-only">{{ text('layout.close') }}</span>
                </button>
            </div>
        @endforeach
    </div>
@endif
