# Baza danych i modele

> **Status:** zaimplementowane są kolumny standardowe (`Database\Columns`), `Models\DataModel`
> (token, soft delete, casty, checksum, `active()`), historia zmian (`Database\History`, triggery
> MySQL i SQLite, `Database\HistoryActor`), krótkie tłumaczenia `json_i18n` (`HasI18n`), pierwsze
> słowniki (lokalizacja, §14) oraz organizacje, członkostwa, uprawnienia spatie i role stref z plików JSON (§15). Nadal koncepcją są: hierarchia `HasParent` (§3), tabele
> `<tabela>_i18n`, RODO (`personalColumns()`, `AnonymizeRecord`, §8) i mappery (§12).

Silnik: MySQL 8 (`DB_CONNECTION=mysql`). Wszystkie zasady obowiązują każdą tabelę aplikacji,
niezależnie od paczki, do której należy jej logika.

## 1. Jedno miejsce schematu

- Migracje **wszystkich** tabel (Core i stref publicznych) leżą w Core: `database/migrations/`
  tej paczki, ładowane przez `AdmiroServiceProvider` (`loadMigrationsFrom`). Publiczne paczki
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
    nie działają `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`. Blokadę zdejmuje tylko
    `admiro.security.allow_destructive_commands` (`ADMIRO_ALLOW_DESTRUCTIVE_COMMANDS=true`), które ustawia
    wdrożenie od zera `vendor/bin/simplysmart deploy:fresh` dla jednego procesu `migrate:fresh`; nigdy w `.env`.
  - Seedery dzielą się na **referencyjne** (słowniki: idempotentne, rekord szukany po kodzie z `codeColumn()`,
    nowy dostaje id z puli seederów (§2), zapis przez model, więc dostaje token, checksum i historię; wolno je uruchomić na produkcji) i **demo**
    (dane przykładowe, na produkcji odmawiają pracy).
  - Seedery to klasy w `src/Database/Seeders/` (`Simplysmart\Admiro\Database\Seeders`, autoload
    paczki), uruchamiane komendami Core (np. `admiro:localization:seed`); szkieletowy
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
  └─ Simplysmart\Admiro\Models\MainModel            (abstract, Core: token jako klucz trasy)
       ├─ Simplysmart\Admiro\Models\MainAuthenticatable   (konta stref, ZoneTables: to samo co DataModel)
       └─ Simplysmart\Admiro\Models\DataModel             (abstract: tabele z kolumnami standardowymi)
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
  - `status` — standardowy stan wiersza Core `Enums\RecordStatus` (`draft`, `pending`, `active`, `blocked`,
    `migrating`, `quarantined`, `archived`; nowy wiersz `active`, cast i wartość domyślna z `HasStandardColumns`).
    Zmienia go tylko `Database\RecordStatusTransitions` — w transakcji z blokadą wiersza, z kontrolą przejść
    (`draft → pending → active`, `active ↔ blocked`, `active → archived`, dowolny → `migrating` → poprzedni,
    `quarantined → active`; stan sprzed migracji w `json_migrations.record_status.previous`, przywraca go
    `finishMigration()`) i wpisem `record status changed` w dzienniku zdarzeń (`Audit\Events\RecordStatusChanged`).
    Pusty `status` istniejących wierszy uzupełnia `Database\RecordStatusBackfill` (zapis przez modele: `checksum`
    i historia spójne). Konto w stanie innym niż `active` nie zaloguje się. Model, który trzyma w `status` stan
    dziedzinowy (np. wizyta: backed enum w `Enums/` paczki z etykietą, kontrakt `Contracts\HasLabel`), nadpisuje
    `usesRecordStatus()` na `false` i sam podaje cast;
  - `is_active` — widoczność dla użytkownika końcowego: admin widzi np. 200 języków, użytkownik
    tylko te, które admin włączył. Wiersz użyteczny w wyborach i procesach to `status = active` i `is_active`
    (scope `usable()`). O tym, czy filtrować, decyduje klasa zapytań na podstawie
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
| Stałe teksty: interfejs, nazwy kolumn, akcje | pliki `lang/<locale>/` paczki | `text_*` (kaskada strefa → admiro → aplikacja) |
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
| `json_tags` | lista tagów rekordu (`["eu", "b2b"]`): wyszukiwanie `JSON_CONTAINS`, meta `article:tag` w `x-admiro::page` |
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

Pierwsze słowniki Core: kraje, języki, waluty, stawki VAT, kursy walut i dni wolne. Wspólne dla
wszystkich stref, więc tabele, modele (`Models\Dictionaries`) i odczyty (`Queries\Dictionaries`)
są w Core; ekrany konfiguracji powstają w strefie administratorów `admiro`.

| Tabela | Model | Kolumny domenowe |
| --- | --- | --- |
| `locale_currencies` | `Currency` | `alpha_code_3` (ISO 4217: `PLN`), `numeric_code` (`985`), `decimals` |
| `locale_languages` | `Language` | `code` (ISO 639-1/2/3: `pl`, `gsw`), `native_name`, `is_interface`, `flag_icon` |
| `locale_countries` | `Country` | `alpha_code_2` (`PL`), `alpha_code_3` (`POL`), `numeric_code` (`616`), `dial_code`, `currency_id`, `is_eu`, `flag_icon` (`pl`), `continent` (`Enums\Continent`), `region_code` (UN M49), `population` |
| `locale_country_language` | `CountryLanguage` (pivot) | `country_id`, `language_id`, `is_primary` |
| `locale_tax_rates` | `TaxRate` | `country_id`, `type` (`Enums\TaxRateType`), `rate`, `valid_from`, `valid_to`, `note` |
| `locale_holidays` | `Holiday` | `country_id`, `holiday_date`, `source` (`Enums\HolidaySource`: `nager`, `manual`), `is_day_off`; `name` w języku kraju, `json_i18n` z nazwą angielską; unikalne kraj + dzień |
| `locale_exchange_rates` | `ExchangeRate` | `currency_id`, `rate_date`, `rate` (PLN za 1 jednostkę, 6 miejsc), `source` (`Enums\ExchangeRateSource`: `nbp`, `manual`), `table_number`; unikalne waluta + dzień |

- `name` jest w języku aplikacji, `json_i18n` ma nazwy w językach interfejsu (`translatedName()`).
- `is_active` — wpis do wyboru w treściach i dokumentach; `is_default` (kolumna standardowa) — kraj,
  język i waluta aplikacji (po jednym). `Queries\Dictionaries\DefaultEntries` zwraca wpis `is_default`, a bez
  niego wpis o kodzie (`codeColumn()`) z configu (`admiro.localization.country`, `app.locale`,
  `admiro.locale.currency`).
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
  ikony. Widok: `<x-admiro::flag :icon="$country->flag_icon" />` (`docs/design-system.md` §7).
- `json_attributes.flag_icons` języka (`Language::FLAG_ICONS`, odczyt `Language::flagIcons()`) — flagi wszystkich
  krajów, w których język jest urzędowy: najpierw `flag_icon`, potem kraje od największej liczby mówiących (ICU,
  `LocalizationData::languageCountryFlagIcons()`); seeder odświeża je przy każdym uruchomieniu. Panel pokazuje je
  w kolumnie kodu słownika i w kreatorze języków.
- Nowa instalacja włącza tylko język aplikacji i języki interfejsu (`APP_LOCALES`); pozostałe języki administrator
  włącza w kreatorze onboardingu (krok „Języki”). Istniejące bazy zachowują swoje wybory.
- Stawki VAT obowiązują od `valid_from` do `valid_to` włącznie; `Queries\Dictionaries\TaxRatesInForce`
  zwraca stawki kraju w danym dniu, podstawową i stawkę aplikacji (bez słownika:
  `admiro.localization.vat_percent`).
- Kursy walut są w złotych: tabela A NBP (`Contracts\ExchangeRateProvider`, domyślnie `ExchangeRates\NbpTableA`,
  zapytania po najwyżej 93 dni, 404 = dni bez publikacji) zapisywana przez `ExchangeRates\ExchangeRateSync` tylko
  dla aktywnych walut, bez nadpisywania kursów ręcznych (`ExchangeRates\ManualExchangeRates`).
  `Queries\Dictionaries\ExchangeRatesOn` zwraca kurs z dnia, kurs z ostatniego dnia publikacji przed datą
  (`lastBefore()` — reguła przeliczeń faktur) i przelicza kwoty przez PLN (`convert()`, bcmath, zaokrąglenie do
  `decimals` waluty docelowej). Synchronizacja: `php artisan admiro:exchange-rates:sync [--from= --to=]`.
- Dni wolne krajów: ogólnokrajowe święta „Public” z publicznego API Nager.Date (`Contracts\HolidayProvider`,
  domyślnie `Holidays\NagerDate`; wysyłany jest tylko kod kraju i rok) zapisywane przez `Holidays\HolidaySync`
  bez nadpisywania dni wolnych ręcznych (`Holidays\ManualHolidays`); dwa święta jednego dnia mają wspólny wpis.
  `Queries\Dictionaries\HolidaysIn` zwraca dni wolne w okresie (także kraju aplikacji — kalendarze stref je
  oznaczają) i mówi, czy dzień jest roboczy. Synchronizacja: `php artisan admiro:holidays:sync [--year=] [--country=]`.
- Dane startowe: `php artisan admiro:localization:seed` (seedery w `src/Database/Seeders/`, nazwy
  z ICU, stawki UE na 2025 r. do weryfikacji w TEDB). Ponowne uruchomienie dopisuje tylko brakujące
  wpisy, a wybory administratora (włączenie, domyślny, usunięte stawki) zostają.

## 14a. Polityka cookies i rejestr zgód

| Tabela | Model | Kolumny |
| --- | --- | --- |
| `cookie_policies` | `CookiePolicy` | standardowe; `version` (null = szkic, unikalny), `published_at`; tytuł i treść Markdown w każdym języku interfejsu w `json_i18n` (`{język: {title, body}}`, bez `HasI18n`) |
| `cookie_consents` | `CookieConsent` | dziennik bez kolumn standardowych i historii: `recorded_at`, `consent_id` (losowy UUID z ciasteczka), `policy_version`, `decision` (`Enums\ConsentDecision`), `categories` (JSON) |

- Szkic jest jeden; zaczyna się od treści bieżącej wersji, publikacja (`Cookies\CookiePolicies::publish()`)
  wymaga tytułu i treści w języku domyślnym i nadaje kolejny numer. Opublikowanej wersji się nie zmienia.
- Rejestr zgód (`Cookies\ConsentRegistry`) nie ma IP, przeglądarki ani danych osobowych; przyjmuje tylko zgodę na
  bieżącą wersję polityki. `admiro:cookies:prune` (codziennie) usuwa wpisy starsze niż
  `COOKIE_CONSENT_RETENTION_DAYS` (domyślnie 1826 dni).

## 14b. Dokumenty prawne

| Tabela | Model | Kolumny |
| --- | --- | --- |
| `legal_documents` | `LegalDocument` | standardowe; `document` (`Enums\LegalDocumentType`: `privacy`, `gdpr`), `version` (null = szkic, unikalny w obrębie dokumentu), `published_at`; tytuł i treść Markdown w każdym języku interfejsu w `json_i18n` (`{język: {title, body}}`, jak w polityce cookies) |

- Każdy dokument ma jeden szkic; zaczyna się od treści bieżącej wersji, publikacja (`Legal\LegalDocuments::publish()`)
  wymaga tytułu i treści w języku domyślnym i nadaje kolejny numer dokumentu. Opublikowanej wersji się nie zmienia;
  wersje czyta `Queries\Legal\LegalDocumentVersions`.
- Publiczne strony to trasy Core `admiro.legal.privacy` (`/polityka-prywatnosci`) i `admiro.legal.gdpr`
  (`/rodo`); bez opublikowanej wersji 404. Spis treści strony powstaje z nagłówków sekcji `##` treści. Strefa linkuje opublikowane dokumenty z `legal_documents` w configu
  (`Legal\LegalLinks`: karta logowania, stopka layoutu aplikacji, e-maile).
- Pierwsze wersje polityki cookies, polityki prywatności i klauzuli RODO publikuje `php artisan admiro:legal:seed`
  (`Database\Seeders\LegalDocumentsSeeder`, treść z `database/seeders/legal/<język>/<dokument>.md`, administrator danych
  z `LEGAL_CONTROLLER_NAME` i `LEGAL_CONTROLLER_EMAIL`); idempotentnie — dokument z wersją albo szkicem zostaje.

## 15. Organizacje i uprawnienia

Uprawnienia stref działają na `spatie/laravel-permission` z włączonymi zespołami. Zespołem jest
**organizacja**, czyli firma korzystająca z aplikacji. Konfigurację spatie ustawia Core
(`Permissions\PermissionDefaults`), a skeleton nie publikuje `config/permission.php`. Nazwy tabel
i kolumn są w `Permissions\PermissionTables`.

| Tabela | Model | Standard | Kolumny domenowe |
| --- | --- | --- | --- |
| `organizations` | `Models\Organization` | pełny, drzewo przez `parent_id` | `is_department`, nazwa skrócona, dane firmy |
| `permissions` | `Models\Permissions\Permission` | pełny | `guard_name` |
| `roles` | `Models\Permissions\Role` | pełny | `organization_id` (null = rola globalna), `guard_name` |
| `model_has_roles`, `model_has_permissions` | — (spatie) | łącząca | `role_id`/`permission_id`, `model_type`, `model_id`, `organization_id` |
| `role_has_permissions` | — (spatie) | łącząca | `permission_id`, `role_id` |
| `permission_denials` | `Models\Permissions\PermissionDenial` | łącząca | `permission_id`, `model_type`, `model_id`, `organization_id` |
| `organization_members` | `Models\Permissions\OrganizationMember` | łącząca | `organization_id`, `model_type`, `model_id` |

- **Zakres:** definicja roli jest globalna dla guardu strefy. Przypisanie to konto + rola + organizacja,
  a sprawdzenie patrzy tylko na bieżącą organizację spatie (`PermissionRegistrar::setPermissionsTeamId()`).
  Rola z `organization_id` to własna rola jednej organizacji. `Role::create()` spatie wpisuje bieżącą
  organizację, więc rola globalna wymaga jawnego `organization_id => null`.
- **Bez bieżącej organizacji nic nie działa (fail closed):** domyślnej organizacji nie ma. Przypisanie
  roli odrzuca baza (`organization_id` NOT NULL), a sprawdzenie uprawnienia daje `false`.
- **Nazwa skrócona** (`short_name`, opcjonalna, do `Organization::SHORT_NAME_MAX_LENGTH` znaków): druga linia
  pod imieniem i nazwiskiem w menu aplikacji; `Organization::shortName()` daje ją, a bez niej pełną nazwę.
- **Dane firmy** (opcjonalne, długości w `Organization::DETAIL_LENGTHS`): `nip` (10 cyfr, `Rules\Nip`, nieunikalny —
  oddział może mieć NIP firmy), `regon`, `krs`, `phone`, `email`, `billing_email` (e-mail do rozliczeń; nowa
  organizacja bez niego dostaje `email`), `street`, `postal_code`, `city` (`Organization::address()`). Formularz
  w panelach: walidacja `Organizations\OrganizationForm` → `OrganizationData`, widok
  `x-admiro::organization.fields` (karty: dane organizacji, kontaktowe, adresowe).
- **Departamenty** (`is_department`, `Organization::isDepartment()`): wewnętrzna agregacja, czyli dział
  jednostki — organizacji bez flagi. Departament zakłada `OrganizationManagement::createDepartment($dane, $jednostka)`;
  leży zawsze bezpośrednio pod jednostką, nie ma pod sobą żadnej organizacji i nie staje się korzeniem drzewa (każde
  naruszenie to `OrganizationChangeRejected`). Ma własnych członków i role (osobny kontekst w przełączniku, nazwa
  „Jednostka · Departament” z `Organization::contextName()`), ale nie ma własnych danych: rekordy
  `OrganizationDataModel` należą do jednostki. `Queries\Organizations\OrganizationUnits` daje jednostkę organizacji
  (`unitIdOf()`, `currentUnitId()` dla bieżącej) i departamenty jednostki (`departments()` aktywne po
  nazwie, `department()` po tokenie). Wyłączenie departamentu (`setActive(false)`) usuwa go z wyboru kont.
- **Organizacja operatora** (`Organization::OPERATOR_ID` = 1, nazwa z `app.name`) jest zakresem stref
  bez firm klientów. Tworzy ją idempotentnie `php artisan admiro:organizations:seed`.
- **Usunięcie organizacji** (fizyczne) kasuje jej role i przypisania (`cascadeOnDelete`), więc rola
  organizacji nigdy nie staje się globalna. Zwykle organizację usuwa się miękko.
- **Odmowy:** wpis w `permission_denials` wygrywa z uprawnieniem z ról i bezpośrednim w tej samej
  organizacji (trait `Permissions\Concerns\HasZonePermissions` nadpisuje `hasPermissionTo()`,
  więc działa też w middleware `zone.permission` i w Gate).
- **Soft delete i nazwy:** usunięta rola lub uprawnienie dalej zajmuje nazwę w guardzie (unikalny
  indeks), jak e-mail usuniętego konta. Spatie nie widzi rekordów w koszu, więc seeder szuka przez
  `withTrashed()` i przywraca rekord.
- **Tabele łączące spatie** mają kolumnę `id` (potrzebną triggerom historii) zamiast złożonego klucza
  głównego. Złożony klucz zostaje unikalnym indeksem. Nie mają `timestamps`, bo spatie ich nie wypełnia.
- Pula id: role, uprawnienia i organizacje z seederów mają id 1–999, a rekordy użytkowników od 1000.
- Cache spatie ma wspólny klucz `zone:shared:spatie.permission` (`ZoneCacheKey`).
- **Członkostwo** konta w organizacji jest niezależne od ról (`organization_members`, relacja
  `ZoneAccount::organizations()`, aktywne przez `Queries\Organizations\ActiveMemberships`).
  `Permissions\OrganizationMembership::add()` dodaje członka bez ról, a `remove()` usuwa razem z nim jego role,
  uprawnienia bezpośrednie i odmowy w tej organizacji oraz unieważnia jego sesje. Bieżącą organizację
  żądania wybiera strefa (`docs/strefy.md`, „Uprawnienia”).
- **Dane organizacji:** model rekordów jednej organizacji dziedziczy po `Models\OrganizationDataModel`
  (kolumna `organization_id` po `Columns::base()`, relacja `organization()`, wiązanie trasy tylko w jednostce bieżącej
  organizacji).

### Role stref z plików JSON

Plik jednej strefy (strefa publiczna: `database/seeders/permissions/<strefa>.json` w Core; paczka prywatna:
własne repozytorium i `PermissionFiles::add($ścieżka)` w jej providerze):

```json
{
    "zone": "shop",
    "permissions": ["orders.view", "orders.manage", "reports.view"],
    "roles": {
        "guest": {"permissions": []},
        "support": {"permissions": ["orders.view"]},
        "clerk": {"permissions": ["orders.view", "orders.manage"]},
        "manager": {"inherits": ["clerk"], "permissions": ["reports.view"]},
        "owner": {"permissions": "*", "except": ["reports.view"], "assigns": ["manager"]}
    }
}
```

- `zone` to nazwa strefy z logowaniem; guard pochodzi z rejestru stref. Role `guest`, `support` i `owner`
  są obowiązkowe (`Permissions\RequiredRole`). Nazwy uprawnień mają postać `zasób.akcja` (kebab-case),
  role kebab-case w liczbie pojedynczej.
- `inherits` i `"*"` z `except` są spłaszczane przy zapisie, więc w bazie nie ma hierarchii ról.
- `assigns` (opcjonalne) to role delegowane: posiadacz roli nadaje je i odbiera mimo braku ich uprawnień,
  także na własnym koncie (np. właściciel bez dostępu do danych wrażliwych, który jest też specjalistą).
  Synchronizacja zapisuje listę w `json_default_settings.assigns` roli (`Permissions\RoleDelegation::SETTING`).
- Etykiet nie ma w pliku: dają je tłumaczenia `permissions.<strefa>.<zasób>.<akcja>` i `roles.<strefa>.<rola>`
  (pl, en), dla stref publicznych w Core, dla paczki z własnym plikiem w jej `lang/`
  (`text('<paczka>::permissions.<strefa>.…')`, kaskada sięga potem do Core). Paczka strefy ma enum
  uprawnień i ról, a test sprawdza jego zgodność z plikiem.
- `Permissions\PermissionFileParser` waliduje plik przed zapisem (nieznana strefa, brak roli obowiązkowej,
  nieznane uprawnienie w roli lub `except`, zła nazwa, duplikat, nieznana rola w `inherits` lub `assigns`, cykl).
- `php artisan admiro:permissions:sync [--zone=]` (oraz seeder `ZonePermissionsSeeder`) najpierw waliduje
  wszystkie pliki, potem w jednej transakcji zapisuje uprawnienia i role globalne guardu strefy z id z puli
  seederów, przywraca usunięte i synchronizuje uprawnienia ról. Jest idempotentna. Rekordy strefy spoza
  pliku tylko raportuje; usuwa je (soft delete) `--prune` z potwierdzeniem, a na produkcji z `--force`.

### Nadawanie ról i właściciele stref

- `Permissions\ZoneRoleAssignment::assign()` / `revoke()` chronią przed eskalacją: nadający jest kontem tej
  samej strefy, oba konta są członkami organizacji (strefa bez `teams`: tylko organizacja operatora,
  członkostwo dopisywane przy nadaniu), a każde uprawnienie roli nadający ma sam w tej organizacji
  (`Permissions\PermissionCoverage`, z odmowami) albo rolę deleguje mu któraś z jego ról w tej organizacji
  (`Permissions\RoleDelegation`, `assigns`; role z innych organizacji się nie liczą). Bez sprawdzania eskalacji role nadaje tylko system
  (`Permissions\SystemRoleGrant`: konsola, zakładanie właścicieli). Odebranie roli unieważnia sesje konta. Każda zmiana trafia do dziennika
  `sign_in` (`ZoneRoleChanged` z aktorem).
- Właściciela strefy zakłada się przez kontrakt `Contracts\ZoneOwnerProvisioner` z rejestru
  `Permissions\ZoneOwnerProvisioners` (paczka strefy wywołuje w providerze `addDefault('<strefa>')`; rejestr trzyma
  fabryki i tworzy provisioner dopiero przy użyciu, bo w `boot()` zależności innych paczek mogą jeszcze nie istnieć).
  Konto powstaje w tabeli strefy docelowej (albo istniejące konto o tym adresie), dostaje członkostwo i rolę
  `owner` w organizacji klienta; strefa bez `teams` używa organizacji operatora.
- **Ustawienia lokalizacji** (`Localization\LocalizationSettings`): kraj, język i waluta domyślne (jeden wpis
  `is_default` w słowniku) oraz języki interfejsu w granicach `APP_LOCALES`; język domyślny musi być językiem
  interfejsu. Wybory daje `Queries\Dictionaries\LocalizationChoices`.
- **Słowniki w panelu** (`Localization\DictionaryManagement`): włączanie i wyłączanie krajów, języków i walut
  (`Localization\Dictionary`; wpisu domyślnego ani języka interfejsu nie da się wyłączyć) oraz stawki VAT: nowa
  stawka od daty (nowa podstawowa kończy poprzednią podstawową dzień wcześniej) i zakończenie stawki
  (`DictionaryChangeRejected` przy złych danych). Nazwy i tłumaczenia stawek buduje `Localization\TaxRateLabels`,
  a języki nazw słowników `Localization\DictionaryLocales` — te same dla seederów i panelu. Pełne listy (także
  wyłączone wpisy) daje `Queries\Dictionaries\DictionaryList`.
- **Dziennik zdarzeń** (`audit_events`, `Models\AuditEvent`): wyjątek od standardu tabel danych — tylko
  dopisywanie, bez kolumn standardowych i historii, konto i aktor jako identyfikator ze strefą (bez klucza obcego),
  IP szyfrowane; odczyt `Queries\Audit\AuditLog`, czyszczenie `admiro:audit:prune`.
- **Zarządzanie organizacjami** (`Organizations\OrganizationManagement`): zakładanie, zmiana nazwy i organizacji
  nadrzędnej (drzewo bez cykli, nadrzędna musi być aktywna), włączanie i wyłączanie; organizacji operatora nie
  wolno wyłączyć ani przenieść (`OrganizationChangeRejected`). Wyłączona organizacja znika z aktywnych członkostw.
  `Organizations\EnrollMember` dopisuje osobę do organizacji w strefie (istniejące konto o tym adresie albo nowe,
  bez ról); z niego korzysta też zakładanie właściciela.
- Panele administracyjne czytają dane przez zapytania Core: `Queries\Accounts\ZoneAccounts` (strona kont strefy,
  szukanie po dokładnym adresie przez `email_hash`, konto po tokenie), `Queries\Permissions\ZoneRoleNames`
  (role strefy i role konta w organizacji), `Queries\Organizations\ActiveOrganizations`, `OrganizationList`
  (także wyłączone) i `OrganizationMembers` (członkowie we wszystkich strefach albo w jednej, konto po tokenie
  tylko z danej organizacji).
