{{-- Zakładka „Pola dodatkowe” karty x-admiro::organization.fields (customFields): pola dodatkowe sekcji organizacji (Attributes\CustomAttributes, section) — w karcie edytowalnej podgląd i edytor w fieldset[data-ss-edit-fields], tylko do odczytu sam podgląd. Zmienne $organization, $disabled, $withPreview z formularza organizacji. --}}
@php($items = $organization !== null ? \Simplysmart\Admiro\Attributes\CustomAttributes::of($organization, $section) : [])
@if ($disabled)
    <x-admiro::attributes-editor :items="$items" :section="$section" readonly />
@else
    <fieldset class="ss-form ss-fieldset" data-ss-edit-fields>
        <x-admiro::attributes-editor :items="$items" :section="$section" :preview="$withPreview" />
    </fieldset>
@endif
