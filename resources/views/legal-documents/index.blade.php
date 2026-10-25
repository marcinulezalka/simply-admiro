{{-- Dokument prawny Core (polityka prywatności albo klauzula RODO): formularz szkicu (tytuł i treść Markdown w każdym języku interfejsu; bez settings.manage tylko do odczytu) z zapisem i publikacją nowej wersji, lista opublikowanych wersji z odnośnikiem do strony dokumentu. $document: Enums\LegalDocumentType, $draft: ?LegalDocument (szkic albo bieżąca wersja), $isDraft: bool, $versions: Collection<LegalDocument>, $locales: list<string>, $defaultLocale: string, $canManage: bool. --}}
<x-mainboard::zone.layout :title="$document->label()">
    <p class="ss-notice"><x-mainboard::icon name="info" /> {{ text('admiro::admin.legal_document_lead') }}</p>
    @error('document') <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <form class="ss-card" method="POST" action="{{ route('admiro.legal-documents.update', ['document' => $document->value]) }}">
        @csrf
        @method('PUT')
        <h2 class="ss-card__title">{{ $isDraft ? text('admiro::admin.cookie_policy_draft') : text('admiro::admin.cookie_policy_new_draft') }}</h2>
        <p class="ss-card__lead ss-muted">{{ text('admiro::admin.cookie_policy_markdown_hint', ['locale' => strtoupper($defaultLocale)]) }}</p>
        <fieldset class="ss-form ss-fieldset" @disabled(! $canManage)>
            @foreach ($locales as $locale)
                @php($content = $draft?->content($locale) ?? ['title' => '', 'body' => ''])
                <x-mainboard::input :id="'title-' . $locale" :name="'title[' . $locale . ']'" :label="text('admiro::admin.cookie_policy_field_title', ['locale' => strtoupper($locale)])" :value="old('title.' . $locale, $content['title'])" maxlength="255" />
                <div class="ss-field">
                    <label class="ss-field__label" for="body-{{ $locale }}">{{ text('admiro::admin.cookie_policy_field_body', ['locale' => strtoupper($locale)]) }}</label>
                    <textarea class="ss-input" id="body-{{ $locale }}" name="body[{{ $locale }}]" rows="14">{{ old('body.' . $locale, $content['body']) }}</textarea>
                </div>
            @endforeach
            @if ($canManage)
                <div class="ss-form-actions">
                    <button class="ss-button" type="submit"><x-mainboard::icon name="check" class="ss-button__icon" />{{ text('admiro::admin.cookie_policy_save') }}</button>
                </div>
            @endif
        </fieldset>
    </form>

    @if ($canManage && $isDraft)
        <form class="ss-card ss-card--tinted" method="POST" action="{{ route('admiro.legal-documents.publish', ['document' => $document->value]) }}">
            @csrf
            <h2 class="ss-card__title">{{ text('admiro::admin.cookie_policy_publish') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('admiro::admin.legal_document_publish_lead') }}</p>
            <div class="ss-form-actions">
                <button class="ss-button ss-button--primary" type="submit">{{ text('admiro::admin.cookie_policy_publish_action') }}</button>
            </div>
        </form>
    @endif

    <section class="ss-card ss-card--flush" aria-labelledby="legal-versions">
        <h2 class="ss-card__title ss-card__title--inset" id="legal-versions">{{ text('admiro::admin.cookie_policy_versions') }}</h2>
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\LegalDocument::class, 'version') }}</th>
                        <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\LegalDocument::class, 'published_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr>
                            <th scope="row" class="ss-table__number">{{ $version->version }}@if ($loop->first) <a class="ss-link" href="{{ route($document->routeName()) }}">{{ text('admiro::admin.cookie_policy_current') }}</a>@endif</th>
                            <td class="ss-table__number">{{ $version->published_at === null ? '—' : local_date($version->published_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="ss-muted">{{ text('admiro::admin.legal_document_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-mainboard::zone.layout>
