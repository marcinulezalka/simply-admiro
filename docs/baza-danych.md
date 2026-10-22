# Baza danych i modele

> **Status:** zaimplementowane są kolumny standardowe (`Database\Columns`), `Models\DataModel`
> (token, soft delete, casty, checksum, `active()`), historia zmian (`Database\History`, triggery
> MySQL i SQLite, `Database\HistoryActor`), krótkie tłumaczenia `json_i18n` (`HasI18n`) i pierwsze
> słowniki (lokalizacja, §14). Nadal koncepcją są: hierarchia `HasParent` (§3), tabele
> `<tabela>_i18n`, RODO (`personalColumns()`, `AnonymizeRecord`, §8) i mappery (§12).

Silnik: MySQL 8 (`DB_CONNECTION=mysql`). Wszystkie zasady obowiązują każdą tabelę aplikacji,
niezależnie od paczki, do której należy jej logika.

## 1. Jedno miejsce schematu

- Migracje **wszystkich** tabel (Core i stref publicznych) leżą w Core: `database/migrations/`
  tej paczki, ładowane przez `MainboardServiceProvider` (`loadMigrationsFrom`). Publiczne paczki
  stref nie mają własnych migracji.
- Wyjątek: paczka **prywatna** (prywatne repozytorium) trzyma własne migracje i seedery, zbudowane
  publicznym API Core (`ZoneTables`, `Columns`, `History`), bo nic o niej nie może trafić do
  publicznego Core (zob. „Private Packages” w CLAUDE.md skeletonu).
- Szkielet zostaje stockowy: jego migracje Laravela (`users`, `cache`, `jobs`) się nie zmieniają.
  Tabelę `users` wyrównuje do standardu migracja Core `add_standard_columns_to_users_table`
  (kolumny standardowe w tej samej kolejności, `users_history`, pula id); `token` jest tam nullable,
  bo stockowy model aplikacji go nie nadaje.
- Tabele strefy z logowaniem (konta, kody logowania, sesje) tworzy jedna migracja strefy w Core
  przez `Database\ZoneTables` (`docs/strefy.md`), więc schemat jest identyczny w każdej strefie.
  Konta (`<strefa>_accounts`) mają pełny standard tabel danych: kolumny standardowe, historię
  `<strefa>_accounts_history`, pulę id i soft delete (adres usuniętego konta zostaje zajęty).
- Nazwa pliku migracji zaczyna się od daty (kolejność) i mówi, czyja jest tabela:
  `2026_10_20_120000_create_locale_languages_table.php`.
- Bezpieczeństwo:
  - Core wywołuje `DB::prohibitDestructiveCommands(app()->isProduction())`, więc na produkcji
    nie działają `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`.
  - Seedery dzielą się na **referencyjne** (słowniki: idempotentne, rekord szukany po kodzie z `codeColumn()`,
    nowy dostaje id z puli seederów (§2), zapis przez model, więc dostaje token, checksum i historię; wolno je uruchomić na produkcji) i **demo**
    (dane przykładowe, na produkcji odmawiają pracy).
  - Seedery to klasy w `src/Database/Seeders/` (`Simplysmart\Mainboard\Database\Seeders`, autoload
    paczki), uruchamiane komendami Core (np. `mainboard:localization:seed`); szkieletowy
    `DatabaseSeeder` zostaje stockowy.
  - Seedery nie zawierają sekretów ani prawdziwych danych osobowych.

## 2. Kolumny standardowe (`Database\Columns`)

Grupy pól powtarzające się w wielu tabelach definiuje Core jako statyczne metody
`Database\Columns`, więc są identyczne wszędzie. To zwykłe metody, nie makra `Blueprint`: PHPStan
i Intelephense widzą ich typy. Nazwy kolumn są stałymi `Database\ColumnNames` — model, mapper
i testy używają tych samych stałych, nie literałów.

Kolejność kolumn w każdej tabeli:

```php
Schema::create('locale_languages', function (Blueprint $table): void {
    Columns::base($table);                  // początek tabeli
    $table->string('code', 8)->unique();    // kolumny domenowe
    Columns::closing($table, i18n: true);   // koniec tabeli
});
app(History::class)->create('locale_languages');   // locale_languages_history + triggery
app(IdSlots::class)->reserve('locale_languages');   // AUTO_INCREMENT od 1000
```

| Metoda | Kolumny |
| --- | --- |
| `Columns::base()` | `id`, `token` (string 64, unique, not null), `parent_id` (FK do tej samej tabeli, nullable, `nullOnDelete`), `name`, `status` (string, nullable), `is_active` (boolean, domyślnie `false`), `is_default` (boolean, domyślnie `false`), `sort_order` (integer, domyślnie 0) |
| `Columns::closing(bool $i18n = false)` | `json_attributes`, `json_default_settings`, `json_tags`, `json_i18n` (tylko gdy `$i18n`), `json_migrations`, `checksum` (char 64, index), `json_archive`, `created_at`, `updated_at`, `deleted_at` |
| `address(?string $prefix)` (koncepcja) | `street`, `building_number`, `apartment_number`, `postal_code`, `city`, `country_code` (2) — z prefiksem, gdy tabela ma kilka adresów (`billing_`, `shipping_`) |

- Nowa grupa powstaje jako metoda `Columns`, gdy ten sam zestaw pól pojawia się w drugiej tabeli.
- **Pule id (`Database\IdSlots`):** id 1–999 to sloty rekordów z seederów i providerów paczek — stałe
  id nadawane przez `IdSlots::next()` (kolejny wolny poniżej 1000; pełna pula to błąd). Rekordy
  użytkowników dostają id od 1000: każda migracja tabeli z `Columns::base()` wywołuje
  `IdSlots::reserve()` (MySQL `AUTO_INCREMENT = 1000`, SQLite `sqlite_sequence`). Pulę mają też konta stref
  i `users`; tabele łączące, kody logowania i sesje stref jej nie mają.
- **Tabele łączące** (np. `locale_country_language`: dwa klucze obce i atrybuty powiązania) nie mają
  kolumn standardowych — tylko `id`, klucze, atrybuty i `timestamps` — ale mają historię.
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

## 4. Historia zmian (`<tabela>_history`)

Każda tabela ma tabelę historii zapisywaną **triggerami MySQL** (`AFTER INSERT/UPDATE/DELETE`),
więc ślad zostaje także po zmianach z konsoli SQL, Tinkera czy zewnętrznego narzędzia.
Bez historii są tylko tabele techniczne (`cache*`, `sessions`, `jobs*`, `failed_jobs`,
`migrations`) i same `*_history`. Nazwa z przyrostkiem układa historię obok jej tabeli
(`locale_countries`, `locale_countries_history`).

| Kolumna `<tabela>_history` | Znaczenie |
| --- | --- |
| `id` | klucz wpisu historii |
| `row_id` | `id` zmienionego rekordu (bez FK — rekord mógł zniknąć) |
| `action` | `insert`, `update`, `delete` |
| `old_values`, `new_values` | JSON całego wiersza przed i po zmianie |
| `actor_zone`, `actor_id`, `request_id` | kto i w jakim żądaniu; `NULL` = system (CLI, SQL) |
| `created_at` | czas zmiany |

- Wiersz zapisany jako JSON, nie jako kopia kolumn: zmiana schematu tabeli nie wymaga `ALTER`
  na tabeli historii.
- Tabelę historii i triggery tworzy helper migracji Core: `app(History::class)->create('tabela')`
  po `Schema::create()`, a `->drop('tabela')` w `down()`. Każda migracja zmieniająca kolumny tabeli
  kończy się `->sync('tabela')`, który przebudowuje triggery; `->trackedColumns('tabela')` zwraca
  kolumny zapisywane przez trigger, a test porównuje je z kolumnami tabeli.
- Dialekty triggerów (`Contracts\HistoryTriggers`): `MysqlHistoryTriggers` (MySQL/MariaDB, aktor ze
  zmiennych sesji) i `SqliteHistoryTriggers` (testy w SQLite, aktor `NULL`).
- **Kto zmienił:** trigger nie zna użytkownika aplikacji. Middleware Core `RecordHistoryActor`
  (w grupie `zone.<strefa>`, po `StartSession`) ustawia przez `Database\HistoryActor` zmienne sesji
  MySQL `@actor_zone`, `@actor_id`, `@request_id` (konto z **jawnie nazwanego guarda strefy**,
  losowy identyfikator żądania) i czyści je po odpowiedzi; trigger je odczytuje. Zadanie kolejki
  działa jako „system” z identyfikatorem zadania (`Listeners\SetHistoryActorForJob`). Konsola
  i SQL zapisują `NULL`. Jedno źródło historii — bez dodatkowego Observera Eloquenta.
- Do sprawdzenia przed wdrożeniem: hosting musi dawać uprawnienie `TRIGGER`, a przy włączonym
  binlogu `log_bin_trust_function_creators` (zob. [wdrozenie.md](wdrozenie.md)).

## 5. Modele

```
Illuminate\Database\Eloquent\Model
  └─ Simplysmart\Mainboard\Models\MainModel            (abstract, Core: token jako klucz trasy)
       ├─ Simplysmart\Mainboard\Models\MainAuthenticatable   (konta stref, ZoneTables: to samo co DataModel)
       └─ Simplysmart\Mainboard\Models\DataModel             (abstract: tabele z kolumnami standardowymi)
            └─ modele paczek (Core i stref), np. Models\Dictionaries\Country
```

`MainModel` generuje token i wiąże trasy po `token`. `DataModel` dokłada: `SoftDeletes`, casty
kolumn z `Columns::base()`/`Columns::closing()`, checksum (`HasChecksum`) i scope'y `active()`, `default()`;
słownik z `json_i18n` dołącza `HasI18n` (`translatedName()`).

- **Wartości domyślne:** kolumna z domyślną wartością w migracji ma tę samą wartość w `$attributes`
  modelu (standardowe dodaje `HasStandardColumns`), więc nowy rekord ma przed zapisem te same dane co
  po odczycie, a jego checksum się zgadza.

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
- Scope'y w modelu to tylko słownik warunków (`active()`, `default()`, `roots()`) używany przez Queries.

## 7. Słowniki i tłumaczenia (i18n)

- Każdy słownik to osobna tabela z prefiksem tematu i nazwą zawartości (`locale_languages`, `locale_units`
  dla słowników lokalizacji), z pełnym standardem kolumn i historią. Jedna wspólna tabela słownikowa urosłaby za szybko.
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
  (AES-256, klucz `APP_KEY`; rotacja przez `APP_PREVIOUS_KEYS`). W bazie — i w `*_history` — leży
  szyfrogram, bo szyfruje Eloquent przed zapisem.
- **Wyszukiwanie po zaszyfrowanym polu:** obok kolumny trzymamy blind index
  `<kolumna>_hash` = HMAC-SHA256 znormalizowanej wartości z osobnym kluczem z konfiguracji Core
  (nie `APP_KEY`), np. `email_hash`, `pesel_hash`. Logowanie po e-mailu szuka po `email_hash`.
- **Anonimizacja (prawo do bycia zapomnianym):** model deklaruje swoje kolumny osobowe
  (`personalColumns()`); usuwa je akcja Core `AnonymizeRecord`, nie model. Rekord zostaje
  (relacje i raporty się zgadzają), dane osobowe są nadpisane, a jego wpisy w `*_history` są
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
  `created_at`, `updated_at`, `deleted_at` — inaczej każdy zapis zmieniałby sumę. `null` liczy się
  jak brak wartości.
- Liczy ją `DataModel` przy zapisie (`computeChecksum()`). Import porównuje checksumę zmapowanego payloadu z zapisaną i
  pomija zapis, gdy są równe.

## 11. Kolumny `json_*`

| Kolumna | Przeznaczenie |
| --- | --- |
| `json_attributes` | dodatkowe atrybuty bez własnej kolumny |
| `json_default_settings` | wartości domyślne, które rekord daje procesom aplikacji (np. kraj: termin płatności faktury); odczyt `defaultSetting('klucz.z.kropkami', $domyślna)`, nie stałe w kodzie |
| `json_tags` | lista tagów rekordu (`["eu", "b2b"]`): wyszukiwanie `JSON_CONTAINS`, meta `article:tag` w `x-mainboard::page` |
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

1. Migracja w `database/migrations/` Core: `Columns::base()`, kolumny domenowe, grupy, `Columns::closing()`.
2. `app(History::class)->create('tabela')` i `app(IdSlots::class)->reserve('tabela')` w tej samej migracji (i `History::drop()` w `down()`).
3. Słownik? Prefiks tematu (`locale_`), kody ISO w `alpha_code_2`/`alpha_code_3`/`numeric_code`
   (`Database\CodeColumns`), model implementuje `Contracts\DictionaryEntry`, `Columns::closing($table, i18n: true)`; treści tłumaczone dynamicznie —
   tabela `<tabela>_i18n`.
4. Model dziedziczy `DataModel`, każda kolumna ma cast, a kolumny z wartością domyślną mają ją w `$attributes`.
5. Etykiety kolumn w `lang/<locale>/data.php` dla każdego włączonego języka.
6. Enum statusu w `Enums/` z etykietami.
7. Relacje jako traity w `Models/Relations/`.
8. Dane osobowe: cast `encrypted`, `<kolumna>_hash` do wyszukiwania, wpis w `personalColumns()`.
9. Seeder referencyjny (idempotentny, id z puli `IdSlots::next()`) lub demo (zablokowany na produkcji).
10. Testy: casty, tłumaczenia, triggery historii zgodne z kolumnami.

## 14. Słowniki lokalizacji

Pierwsze słowniki Core: kraje, języki, waluty i stawki VAT. Wspólne dla
wszystkich stref, więc tabele, modele (`Models\Dictionaries`) i odczyty (`Queries\Dictionaries`)
są w Core; ekrany konfiguracji powstają w strefie administratorów `admiro`.

| Tabela | Model | Kolumny domenowe |
| --- | --- | --- |
| `locale_currencies` | `Currency` | `alpha_code_3` (ISO 4217: `PLN`), `numeric_code` (`985`), `decimals` |
| `locale_languages` | `Language` | `code` (ISO 639-1/2/3: `pl`, `gsw`), `native_name`, `is_interface`, `flag_icon` |
| `locale_countries` | `Country` | `alpha_code_2` (`PL`), `alpha_code_3` (`POL`), `numeric_code` (`616`), `dial_code`, `currency_id`, `is_eu`, `flag_icon` (`pl`), `continent` (`Enums\Continent`), `region_code` (UN M49), `population` |
| `locale_country_language` | `CountryLanguage` (pivot) | `country_id`, `language_id`, `is_primary` |
| `locale_tax_rates` | `TaxRate` | `country_id`, `type` (`Enums\TaxRateType`), `rate`, `valid_from`, `valid_to`, `note` |

- `name` jest w języku aplikacji, `json_i18n` ma nazwy w językach interfejsu (`translatedName()`).
- `is_active` — wpis do wyboru w treściach i dokumentach; `is_default` (kolumna standardowa) — kraj,
  język i waluta aplikacji (po jednym). `Queries\Dictionaries\DefaultEntries` zwraca wpis `is_default`, a bez
  niego wpis o kodzie (`codeColumn()`) z configu (`mainboard.localization.country`, `app.locale`,
  `mainboard.locale.currency`).
- Kody ISO mają jedną konwencję (`Database\CodeColumns`): `alpha_code_2`, `alpha_code_3` (wielkie
  litery), `numeric_code` (trzy cyfry z zerami wiodącymi). Języki zostają przy `code`, bo ich kody
  mieszają ISO 639-1, -2 i -3. Kolumnę kodu modelu zwraca `DictionaryEntry::codeColumn()`.
- Dane terytorialne krajów z ICU: `continent` (Europa, Azja, Ameryka Płn. z Ameryką Środkową
  i Karaibami, Ameryka Płd., Afryka, Oceania, Antarktyda), `region_code` (region UN M49, np. `151`
  — Europa Wschodnia) i `population`. Współrzędnych i powierzchni ICU nie ma — dojdą z osobnego źródła.
- Listy krajów są pogrupowane kontynentami: `sort_order` = blok kontynentu
  (`LocalizationData::CONTINENT_ORDER`, po 1000 pozycji) + pozycja kraju z `LEADING_COUNTRIES`
  (pozostałe na końcu bloku, dosortowane po nazwie: `orderBy('sort_order')->orderBy('name')`).
- `is_interface` — język interfejsu, zawsze w granicach `APP_LOCALES` (`docs/strefy.md`).
- `flag_icon` — kod ikony flagi w zestawie flag-icons (`pl`, `gb-eng`, `eu`; ten sam w Iconify jako
  `flag:<kod>-4x3`). Kraj dostaje swój kod ISO, język — flagę kraju: wyjątek z
  `LocalizationData::LANGUAGE_FLAGS` (`en` → `gb`, `ar` → `sa`), kraj o tym samym kodzie, w którym
  język jest urzędowy, albo kraj z największą liczbą mówiących (ICU). Seeder nie nadpisuje ustawionej
  ikony. Widok: `<x-mainboard::flag :icon="$country->flag_icon" />` (`docs/design-system.md` §7).
- Stawki VAT obowiązują od `valid_from` do `valid_to` włącznie; `Queries\Dictionaries\TaxRatesInForce`
  zwraca stawki kraju w danym dniu, podstawową i stawkę aplikacji (bez słownika:
  `mainboard.localization.vat_percent`).
- Dane startowe: `php artisan mainboard:localization:seed` (seedery w `src/Database/Seeders/`, nazwy
  z ICU, stawki UE na 2025 r. do weryfikacji w TEDB). Ponowne uruchomienie dopisuje tylko brakujące
  wpisy, a wybory administratora (włączenie, domyślny, usunięte stawki) zostają.
