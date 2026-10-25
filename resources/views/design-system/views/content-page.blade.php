{{-- Gotowy widok: strona treści (polityka prywatności) na prawdziwym layoucie x-mainboard::content.layout — spis treści z kluczy sekcji, treść w .ss-prose i karta „Zobacz także”; dane fikcyjne. $view: DesignSystemView. --}}
@php
    $sections = ['administrator', 'purposes', 'retention', 'rights', 'changes'];
    $section = static fn (string $key, string $field): string => text('design_system.demo.content.sections.' . $key . '.' . $field);
@endphp
<x-mainboard::content.layout
    :title="text('design_system.demo.content.title')"
    :eyebrow="text('design_system.demo.content.eyebrow')"
    :lead="text('design_system.demo.content.lead')"
    :back="route('mainboard.design-system')"
    :toc="array_map(static fn (string $key): array => ['id' => $key, 'label' => $section($key, 'title')], $sections)"
    robots="noindex, nofollow"
>
    @foreach ($sections as $key)
        <h2 id="{{ $key }}">{{ $section($key, 'title') }}</h2>
        <p>{{ $section($key, 'body') }}</p>
        @if ($key === 'purposes')
            <ul>
                @foreach (range(0, 2) as $item)
                    <li>{{ $section($key, 'items.' . $item) }}</li>
                @endforeach
            </ul>
        @endif
    @endforeach

    <x-slot:related>
        @foreach (['cookies' => 'info', 'terms' => 'clipboard'] as $page => $icon)
            <a class="ss-related" href="{{ route('mainboard.design-system.view', $view) }}">
                <x-mainboard::icon :name="$icon" class="ss-icon--tile" />
                <span class="ss-related__body">
                    <span class="ss-related__title">{{ text('design_system.demo.content.related.' . $page . '.title') }}</span>
                    <span class="ss-related__text">{{ text('design_system.demo.content.related.' . $page . '.text') }}</span>
                </span>
                <x-mainboard::icon name="arrow-right" class="ss-related__arrow" />
            </a>
        @endforeach
    </x-slot:related>
</x-mainboard::content.layout>
