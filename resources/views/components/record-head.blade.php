{{-- Wiersz nagłówka listy wierszy z kolumnami (ul.ss-record-rows.ss-record-rows--columns, .ss-record-row--head): na początku tytuł kolumny nazwy (np. „Jednostka”, nazwa grupy „Kooperacje”), dalej etykiety kolumn faktów w kolejności faktów wierszy i niewidoczne miejsce na główną akcję wiersza, więc kolumny wyrównują się z wierszami. Widoczny tylko w układzie listy (w siatce i bez skryptu układu ukryty); dla czytników ekranu ukryty — wiersze mają własne etykiety faktów. title: string, columns: list<string>. --}}
@props(['title', 'columns' => []])
<li class="ss-record-row ss-record-row--head" aria-hidden="true">
    <span class="ss-record-row__main">{{ $title }}</span>
    <span class="ss-record-row__facts">
        @foreach ($columns as $column)
            <span>{{ $column }}</span>
        @endforeach
    </span>
    <span class="ss-record-row__side"><span class="ss-icon-button ss-record-row__spacer" data-ss-row-action></span></span>
</li>
