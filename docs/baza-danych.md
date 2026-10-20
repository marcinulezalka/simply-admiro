# Baza danych i modele

> **Status: koncepcja.** Dokument opisuje docelowe zasady. Mechanizmy Core wymienione niżej
> (makra, `MainModel`, historia, mappery) nie są jeszcze zaimplementowane — powstają w kolejnych
> krokach i wtedy ten dokument staje się ich opisem.

Silnik: MySQL 8 (`DB_CONNECTION=mysql`). Wszystkie zasady obowiązują każdą tabelę aplikacji,
niezależnie od paczki, do której należy jej logika.

## 1. Jedno miejsce schematu

- Migracje i seedery **wszystkich** tabel (Core i stref) leżą w Core:
  `database/migrations/` i `database/seeders/` tej paczki, ładowane przez
  `MainboardServiceProvider` (`loadMigrationsFrom`). Paczki stref nie mają własnych migracji.
- Szkielet zostaje stockowy: jego migracje Laravela (`users`, `cache`, `jobs`) się nie zmieniają.
- Tabele strefy z logowaniem (konta, kody logowania, sesje) tworzy jedna migracja strefy w Core
  przez `Database\ZoneTables` (`docs/strefy.md`), więc schemat jest identyczny w każdej strefie.
- Nazwa pliku migracji zaczyna się od daty (kolejność) i mówi, czyja jest tabela:
  `2026_10_20_120000_create_dict_languages_table.php`.
- Bezpieczeństwo:
  - Core wywołuje `DB::prohibitDestructiveCommands(app()->isProduction())`, więc na produkcji
    nie działają `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`.
  - Seedery dzielą się na **referencyjne** (słowniki: idempotentny `upsert` po `token` lub `code`,
    wolno je uruchomić na produkcji) i **demo** (dane przykładowe, na produkcji odmawiają pracy).
  - Seedery nie zawierają sekretów ani prawdziwych danych osobowych.

## 2. Builder pól (makra `Blueprint`)

Grupy pól powtarzające się w wielu tabelach definiuje Core jako makra `Blueprint`, więc są
identyczne wszędzie. Nazwy kolumn każdej grupy są stałymi w Core — model, mapper i testy używają
tych samych stałych, nie literałów.

Kolejność kolumn w każdej tabeli:

```php
Schema::create('dict_languages', function (Blueprint $table): void {
    $table->baseColumns();       // początek tabeli
    $table->string('code', 8)->unique();   // kolumny domenowe
    $table->addressColumns('billing_');    // grupy domenowe (opcjonalnie)
    $table->closingColumns();    // koniec tabeli
});
```

| Makro | Kolumny |
| --- | --- |
| `baseColumns()` | `id`, `token` (string 64, unique, not null), `parent_id` (FK do tej samej tabeli, nullable, `nullOnDelete`), `name`, `status` (string), `is_active` (boolean, domyślnie `false`), `sort_order` (integer, domyślnie 0) |
| `addressColumns(?string $prefix)` | `street`, `building_number`, `apartment_number`, `postal_code`, `city`, `country_code` (2) — z prefiksem, gdy tabela ma kilka adresów (`billing_`, `shipping_`) |
| `closingColumns(bool $i18n = false)` | `json_attributes`, `json_i18n` (tylko gdy `$i18n`), `json_migrations`, `checksum` (char 64, index), `json_archive`, `created_at`, `updated_at`, `deleted_at` |

- Nowa grupa powstaje jako makro, gdy ten sam zestaw pól pojawia się w drugiej tabeli.
- `status` to zwykły `string`, nie MySQL `ENUM` — dozwolone wartości trzyma enum PHP (punkt 5),
  więc nowy status nie wymaga migracji.
- **Flagi:** `$table->boolean('is_…')` tworzy w MySQL `TINYINT(1)`. Zawsze `boolean()` w migracji
  i cast `'boolean'` w modelu; nigdy ręczny `tinyInteger` dla flag. W SQL i triggerach flagi mają
  wartości `0`/`1`.

## 3. Hierarchia (`parent_id`)

- Każda tabela ma `parent_id` (z `baseColumns()`), więc każda może być drzewem: kategorie,
  działy, kraj → województwo → powiat, zależne słowniki.
- Model korzysta z traitu Core `HasParent`: relacje `parent()`, `children()`, scope `roots()`.
- Całe poddrzewo pobiera klasa zapytań przez `WITH RECURSIVE` (MySQL 8). Kolumn `path`/`depth`
  nie dodajemy, dopóki pomiar nie pokaże problemu z wydajnością.

## 4. Historia zmian (`history_<tabela>`)

Każda tabela ma tabelę historii zapisywaną **triggerami MySQL** (`AFTER INSERT/UPDATE/DELETE`),
więc ślad zostaje także po zmianach z konsoli SQL, Tinkera czy zewnętrznego narzędzia.
Bez historii są tylko tabele techniczne (`cache*`, `sessions`, `jobs*`, `failed_jobs`,
`migrations`) i same `history_*`.

| Kolumna `history_<tabela>` | Znaczenie |
| --- | --- |
| `id` | klucz wpisu historii |
| `row_id` | `id` zmienionego rekordu (bez FK — rekord mógł zniknąć) |
| `action` | `insert`, `update`, `delete` |
| `old_values`, `new_values` | JSON całego wiersza przed i po zmianie |
| `actor_zone`, `actor_id`, `request_id` | kto i w jakim żądaniu; `NULL` = system (CLI, SQL) |
| `created_at` | czas zmiany |

- Wiersz zapisany jako JSON, nie jako kopia kolumn: zmiana schematu tabeli nie wymaga `ALTER`
  na tabeli historii.
- Tabelę historii i triggery tworzy helper migracji Core (`History::create('tabela')`). Każda
  migracja zmieniająca kolumny tabeli kończy się `History::sync('tabela')`, który przebudowuje
  triggery; test porównuje kolumny tabeli z kolumnami w triggerach.
- **Kto zmienił:** trigger nie zna użytkownika aplikacji. Middleware Core ustawia na połączeniu
  zmienne sesji MySQL `@actor_zone`, `@actor_id`, `@request_id` (użytkownik z **jawnie nazwanego
  guarda strefy**), a trigger je odczytuje. Worker kolejki ustawia i czyści je na każde zadanie.
  Jedno źródło historii — bez dodatkowego Observera Eloquenta.
- Do sprawdzenia przed wdrożeniem: hosting musi dawać uprawnienie `TRIGGER`, a przy włączonym
  binlogu `log_bin_trust_function_creators` (zob. [wdrozenie.md](wdrozenie.md)).

## 5. Modele

```
Illuminate\Database\Eloquent\Model
  └─ Simplysmart\Mainboard\Models\MainModel            (abstract, Core)
       ├─ Simplysmart\Mainboard\Models\MainAuthenticatable   (tabele użytkowników stref)
       └─ modele paczek (Core i stref)
```

`MainModel` zapewnia: `SoftDeletes`, wiązanie tras po `token`, generowanie tokenu, checksum,
casty kolumn z `baseColumns()`/`closingColumns()` i scope `active()`.

**Model jest czysty:** mapuje tabelę, typy i relacje. Nie zawiera logiki biznesowej, zapytań
wieloetapowych ani odwołań do `Auth`/`auth()`.

- **Typy:** każda kolumna ma cast (`boolean`, `integer`, `decimal:2`, `datetime`, `array`,
  enum, `encrypted`…). Test sprawdza, że każda kolumna tabeli jest w `casts()` modelu.
- **Nazwy kolumn:** każda kolumna ma etykietę w `lang/<locale>/data.php` paczki, czytaną
  istniejącym helperem `text_data($model, $kolumna)` (`data.<model>.<kolumna>`, potem
  `data.<kolumna>`). Test sprawdza kompletność dla każdego włączonego języka.
- **Relacje tylko w traitach:** `Models/Relations/Has<X>` / `BelongsTo<X>` w paczce, do której
  należy model docelowy. Model tylko je dołącza (`use HasAddresses;`). Strefy nie mają relacji
  między sobą (izolacja stref).
- **Flagi `is_` i `has_`:**
  - `is_*` — zapisany stan rekordu (`is_active`, `is_verified`), kolumna boolean;
  - `has_*` — posiadanie cechy lub relacji (`has_newsletter`, `has_children`); kolumna tylko gdy
    trzeba po niej filtrować, inaczej `withExists()` w zapytaniu;
  - nazwy twierdzące, bez zaprzeczeń (`is_active`, nie `is_hidden`).
- **`status` i `is_active` — dwie różne rzeczy:**
  - `status` — cykl życia rekordu (np. `draft`, `pending`, `approved`, `archived`), backed enum
    (string) w `Enums/` paczki, z etykietą z tłumaczeń (kontrakt Core `Contracts\HasLabel`);
  - `is_active` — widoczność dla użytkownika końcowego: admin widzi np. 200 języków, użytkownik
    tylko te, które admin włączył. O tym, czy filtrować, decyduje klasa zapytań na podstawie
    jawnego guarda strefy, nie model.

## 6. Warstwy

```
Kontroler → Form Request (walidacja) → Action (zapis) / Query (odczyt) → DTO → widok lub Resource
```

- **Kontroler** nie zawiera zapytań: przyjmuje żądanie, wywołuje akcję lub zapytanie, zwraca
  odpowiedź.
- **Queries/** — odczyt danych (filtry, sortowanie, paginacja, `is_active` wg guarda).
- **Actions/** — zmiany danych, transakcje, reguły biznesowe.
- Scope'y w modelu to tylko słownik warunków (`active()`, `roots()`) używany przez Queries.

## 7. Słowniki i tłumaczenia (i18n)

- Każdy słownik to osobna tabela z prefiksem `dict_` (`dict_languages`, `dict_units`), z pełnym
  standardem kolumn i historią. Jedna wspólna tabela słownikowa urosłaby za szybko.
- Trzy poziomy tłumaczeń, dobierane do rodzaju danych:

| Dane | Gdzie | Odczyt |
| --- | --- | --- |
| Stałe teksty: interfejs, nazwy kolumn, akcje | pliki `lang/<locale>/` paczki | `text_*` (kaskada strefa → mainboard → aplikacja) |
| Krótkie słowniki: statusy, typy, jednostki | kolumna `json_i18n` (`{"pl": "polski", "en": "Polish"}`) | cast `array`, wartość dla bieżącego języka |
| Treści dynamiczne: produkt, opis, artykuł | tabela `<tabela>_i18n` | relacja z traitu `HasTranslations` |

- Tabela `<tabela>_i18n`: `id`, `row_id` (FK), `locale`, tłumaczone kolumny, `created_at`,
  `updated_at`; unikalny indeks (`row_id`, `locale`). Ma własną historię.
- Jak w `text_*`: brak tłumaczenia w bieżącym języku nie spada na inny język — pokazuje klucz
  (`code`), więc brak jest od razu widoczny.

## 8. Dane osobowe (RODO)

- **Szyfrowanie:** kolumny z danymi osobowymi mają cast `encrypted` / `encrypted:array`
  (AES-256, klucz `APP_KEY`; rotacja przez `APP_PREVIOUS_KEYS`). W bazie — i w `history_*` — leży
  szyfrogram, bo szyfruje Eloquent przed zapisem.
- **Wyszukiwanie po zaszyfrowanym polu:** obok kolumny trzymamy blind index
  `<kolumna>_hash` = HMAC-SHA256 znormalizowanej wartości z osobnym kluczem z konfiguracji Core
  (nie `APP_KEY`), np. `email_hash`, `pesel_hash`. Logowanie po e-mailu szuka po `email_hash`.
- **Anonimizacja (prawo do bycia zapomnianym):** model deklaruje swoje kolumny osobowe
  (`personalColumns()`); usuwa je akcja Core `AnonymizeRecord`, nie model. Rekord zostaje
  (relacje i raporty się zgadzają), dane osobowe są nadpisane, a jego wpisy w `history_*` są
  czyszczone z tych kolumn — to jedyny dozwolony wyjątek od niezmienności historii.

## 9. Token zamiast `id`

- Publiczne adresy i API używają wyłącznie `token`; `id` służy tylko kluczom obcym i kodowi
  wewnątrz aplikacji (ochrona przed IDOR i szacowaniem liczby rekordów).
- `MainModel` generuje token przy tworzeniu (losowy, kryptograficzny). Nie ULID: ULID zdradza
  czas utworzenia rekordu.
- `getRouteKeyName()` zwraca `token`, więc wiązanie modeli w trasach działa bez dodatkowego kodu.

## 10. Checksum rekordu

- `checksum` = SHA-256 z kanonicznego JSON (posortowane klucze) **kolumn domenowych** rekordu.
- Pomija kolumny techniczne: `id`, `token`, `checksum`, `json_migrations`, `json_archive`,
  `created_at`, `updated_at`, `deleted_at` — inaczej każdy zapis zmieniałby sumę.
- Liczy ją `MainModel` przy zapisie. Import porównuje checksumę zmapowanego payloadu z zapisaną i
  pomija zapis, gdy są równe.

## 11. Kolumny `json_*`

| Kolumna | Przeznaczenie |
| --- | --- |
| `json_attributes` | dodatkowe atrybuty bez własnej kolumny |
| `json_i18n` | tłumaczenia krótkich słowników (punkt 7) |
| `json_migrations` | metadane integracji: źródło, `external_id`, czas synchronizacji, wersja |
| `json_archive` | stare wartości i pola wycofane po migracjach danych |

Pole, po którym trzeba filtrować lub sortować, dostaje własną kolumnę, nie klucz w JSON.

## 12. Mappery

Mapper tłumaczy dane z zewnętrznego systemu (ERP, API, CSV) na kolumny modelu i z powrotem:
nazwy pól i typy.

```php
final class ErpProductMapper extends FieldMapper
{
    /** @return array<string, array{0: string, 1: string}> pole zewnętrzne => [kolumna, typ] */
    protected function fields(): array
    {
        return [
            'product_title' => ['name', 'string'],
            'qty'           => ['stock_quantity', 'integer'],
            'active_flag'   => ['is_active', 'boolean'],
            'created'       => ['created_at', 'datetime'],
        ];
    }
}
```

- Osobna klasa na źródło (`Mappers/<Źródło><Model>Mapper`), bo jeden model ma zwykle kilka
  źródeł. Model zostaje czysty.
- Bazowy `FieldMapper` Core (kontrakt `Contracts\FieldMapper`) daje `toModel()` i `fromModel()`
  oraz wspólne sprzątanie: `trim`, pusty tekst → `null`, daty, flagi, enumy.
- Wynik mappera trafia do akcji importu, która porównuje checksum (punkt 10).

## 13. Lista kontrolna nowej tabeli

1. Migracja w `database/migrations/` Core: `baseColumns()`, kolumny domenowe, grupy, `closingColumns()`.
2. `History::create('tabela')` w tej samej migracji.
3. Słownik? Prefiks `dict_` i `closingColumns(i18n: true)`; treści tłumaczone dynamicznie —
   tabela `<tabela>_i18n`.
4. Model dziedziczy `MainModel`, każda kolumna ma cast.
5. Etykiety kolumn w `lang/<locale>/data.php` dla każdego włączonego języka.
6. Enum statusu w `Enums/` z etykietami.
7. Relacje jako traity w `Models/Relations/`.
8. Dane osobowe: cast `encrypted`, `<kolumna>_hash` do wyszukiwania, wpis w `personalColumns()`.
9. Seeder referencyjny (idempotentny) lub demo (zablokowany na produkcji).
10. Testy: casty, tłumaczenia, triggery historii zgodne z kolumnami.
