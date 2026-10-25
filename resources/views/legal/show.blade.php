{{-- Strona dokumentu prawnego (publiczna, layout treści Core): tytuł z ikoną dokumentu, wersja i data publikacji, wstęp, treść bieżącej wersji z Markdown (bez surowego HTML-a), „Wstecz” nad i pod treścią oraz karta „Zobacz także” z pozostałymi opublikowanymi dokumentami. $type: Enums\LegalDocumentType, $document: Models\LegalDocument, $title: string, $html: string, $back: string, $related: list<array{url: string, title: string, text: string, icon: string}>. --}}
<x-admiro::content.layout :icon="$type->icon()" :title="$title" :eyebrow="text('legal.eyebrow', ['version' => (string) $document->version, 'date' => local_date($document->published_at)])" :lead="$type->lead()" :back="$back">
    {!! $html !!}

    @if ($related !== [])
        <x-slot:related>
            @include('admiro::legal.related', ['related' => $related])
        </x-slot:related>
    @endif
</x-admiro::content.layout>
