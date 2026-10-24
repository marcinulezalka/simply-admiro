{{-- Strona polityki cookies (publiczna, layout treści Core): treść bieżącej wersji z Markdown (bez surowego HTML-a), wersja i data publikacji oraz tabela cookies aplikacji (nazwa, kategoria, cel, czas) i przycisk ustawień cookies. $policy: CookiePolicy, $title: string, $html: string, $cookies: list<Cookies\CookieDescription>. --}}
<x-mainboard::content.layout :title="$title !== '' ? $title : text('cookies.links.policy')" :eyebrow="text('cookies.policy.eyebrow', ['version' => (string) $policy->version, 'date' => local_date($policy->published_at)])" :toc="[['id' => 'cookies-content', 'label' => text('cookies.policy.content')], ['id' => 'cookies-list', 'label' => text('cookies.policy.list')]]">
    <section id="cookies-content">
        {!! $html !!}
    </section>
    <section id="cookies-list">
        <h2>{{ text('cookies.policy.list') }}</h2>
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('cookies.policy.name') }}</th>
                        <th scope="col">{{ text('cookies.policy.category') }}</th>
                        <th scope="col">{{ text('cookies.policy.purpose') }}</th>
                        <th scope="col">{{ text('cookies.policy.lifetime') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cookies as $cookie)
                        <tr>
                            <th scope="row"><code>{{ $cookie->name }}</code></th>
                            <td>{{ $cookie->category->label() }}</td>
                            <td>{{ $cookie->purpose() }}</td>
                            <td>{{ $cookie->lifetime() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p><button class="ss-button" type="button" data-ss-cookie-settings>{{ text('cookies.links.settings') }}</button></p>
    </section>
</x-mainboard::content.layout>
