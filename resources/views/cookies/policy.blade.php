{{-- Strona polityki cookies (publiczna, layout treści Core): treść bieżącej wersji z Markdown (bez surowego HTML-a), wersja i data publikacji oraz lista cookies aplikacji jako panele na całą szerokość (nazwa, kategoria, cel, czas; długie wartości zawijane) i przycisk ustawień cookies z ikoną; tytuł z ikoną ciasteczka. „Wstecz” nad i pod treścią i karta „Zobacz także” z opublikowanymi dokumentami prawnymi. $policy: CookiePolicy, $title: string, $html: string, $cookies: list<Cookies\CookieDescription>, $back: string, $related: list<array{url: string, title: string, text: string, icon: string}>. --}}
<x-mainboard::content.layout icon="cookie" :back="$back" :title="$title !== '' ? $title : text('cookies.links.policy')" :eyebrow="text('cookies.policy.eyebrow', ['version' => (string) $policy->version, 'date' => local_date($policy->published_at)])" :toc="[['id' => 'cookies-content', 'label' => text('cookies.policy.content')], ['id' => 'cookies-list', 'label' => text('cookies.policy.list')]]">
    <section id="cookies-content">
        {!! $html !!}
    </section>
    <section id="cookies-list">
        <h2>{{ text('cookies.policy.list') }}</h2>
        <ul class="ss-cookie-list">
            @foreach ($cookies as $cookie)
                <li class="ss-card ss-card--muted ss-cookie-list__item">
                    <div class="ss-cookie-list__head">
                        <code class="ss-cookie-list__name">{{ $cookie->name }}</code>
                        <span class="ss-badge">{{ $cookie->category->label() }}</span>
                    </div>
                    <dl class="ss-cookie-list__details">
                        <div>
                            <dt>{{ text('cookies.policy.purpose') }}</dt>
                            <dd>{{ $cookie->purpose() }}</dd>
                        </div>
                        <div>
                            <dt>{{ text('cookies.policy.lifetime') }}</dt>
                            <dd>{{ $cookie->lifetime() }}</dd>
                        </div>
                    </dl>
                </li>
            @endforeach
        </ul>
        <p><button class="ss-button" type="button" data-ss-cookie-settings><x-mainboard::icon name="settings" class="ss-button__icon" />{{ text('cookies.links.settings') }}</button></p>
    </section>

    @if ($related !== [])
        <x-slot:related>
            @include('mainboard::legal.related', ['related' => $related])
        </x-slot:related>
    @endif
</x-mainboard::content.layout>
