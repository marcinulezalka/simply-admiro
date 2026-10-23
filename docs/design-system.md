# Design system

> **Status: koncepcja.** Dziś obowiązuje statyczny CSS na tokenach (`public/assets/css/tokens.css`,
> `base.css`, `app.css`); tokeny semantyczne z §2 i strony logowania już go używają. Dokument opisuje docelowy wygląd interfejsu i przejście paczek na
> Tailwind 4 z własnym Vite. Tokeny, komponenty i build powstają w kolejnych krokach.

## 1. Filozofia: Clean & Soft Enterprise

Funkcjonalność systemów B2B (dane, procesy, statusy) z lekkością interfejsów konsumenckich:

- dużo przestrzeni, miękkie zaokrąglenia, łagodny kontrast;
- czytelna hierarchia: szara, drobna etykieta → ciemna, wyraźna wartość;
- białe karty na bardzo jasnym tle zamiast ciężkich tabel, ostrych krawędzi i głębokich cieni;
- akcent tylko tam, gdzie użytkownik ma coś zrobić.

## 2. Tokeny

Wartości (kolory, promienie, cienie, fonty, odstępy) są wyłącznie w tokenach. Komponenty i
widoki używają tokenów **semantycznych** (`--ss-color-surface`), nigdy palety bazowej ani wartości
wpisanych na sztywno — zasada z obecnego `tokens.css` zostaje. Kolumna Tailwind pokazuje
odpowiednik, którym opisujemy wygląd w rozmowach i promptach.

| Token | Rola | Jasny | Ciemny | Tailwind (jasny) |
| --- | --- | --- | --- | --- |
| `--ss-color-bg` | tło strony | `#f8fafc` | `#020617` | `bg-slate-50` |
| `--ss-color-surface` | karta, panel, top bar | `#ffffff` | `#0f172a` | `bg-white` |
| `--ss-color-surface-muted` | karta w karcie, pole wyszukiwarki, aktywna pigułka | `#f1f5f9` | `#1e293b` | `bg-slate-100` |
| `--ss-color-border` | ramka karty | `#f3f4f6` | `#1e293b` | `border-gray-100` |
| `--ss-color-border-strong` | ramka pola formularza, separator | `#e5e7eb` | `#334155` | `border-gray-200` |
| `--ss-color-text` | wartości, nagłówki | `#111827` | `#f9fafb` | `text-gray-900` |
| `--ss-color-muted` | etykiety, metadane | `#6b7280` | `#9ca3af` | `text-gray-500` |
| `--ss-color-accent` | akcja główna, element aktywny | `#1f2937` (grafit) | `#f3f4f6` | `bg-gray-800` |
| `--ss-color-accent-hover` | hover akcji głównej | `#111827` | `#ffffff` | `bg-gray-900` |
| `--ss-color-on-accent` | tekst na akcencie | `#ffffff` | `#111827` | `text-white` |
| `--ss-radius-card` | karty, panele, modale | `1rem` | | `rounded-2xl` |
| `--ss-radius-control` | przyciski, pola | `0.75rem` | | `rounded-xl` |
| `--ss-radius-pill` | pigułki, awatar, wyszukiwarka | `9999px` | | `rounded-full` |
| `--ss-shadow-raised` | elementy wyniesione | | | `shadow-sm` |
| `--ss-shadow-overlay` | modal, dropdown | | | `shadow-lg` |

- **Logo Laravela** (`public/assets/img/vendor/laravel.svg`, z oficjalnego repozytorium
  `laravel/art`, znak towarowy Laravel Holdings) występuje tylko jako informacja „Zbudowane na”,
  monochromatycznie przez `currentColor`, nigdy w czerwieni ani jako element marki simplysmart.
- **Akcent to grafit**, spójny z czarno-białą marką. Logo zostaje wyłącznie czarne albo białe
  (zob. [brand/README.md](../brand/README.md)).
- Kolory stanów (sukces, ostrzeżenie, błąd, informacja) dostają osobne tokeny semantyczne
  przy pierwszym komponencie, który ich potrzebuje; zawsze z tekstem lub ikoną, nie samym kolorem.
  Istnieje `--ss-color-danger` (`#b91c1c` / ciemny `#f87171`): błędy pól formularza.
- Logo i panel marki używają `--ss-color-brand` / `--ss-color-on-brand` (czerń albo biel), nie
  `--ss-color-text`, bo grafitowy tekst nie jest kolorem marki.
- Typografia: `--ss-font-sans` (font systemowy, dopóki marka nie ma własnego). Skala:

| Element | Tailwind | Kolor |
| --- | --- | --- |
| Metadane nad tytułem, etykiety pól | `text-xs` / `text-sm` | `--ss-color-muted` |
| Wartości, treść | `text-sm` / `text-base` | `--ss-color-text` |
| Tytuł karty | `text-base font-semibold` | `--ss-color-text` |
| Tytuł strony (nagłówek kontekstowy) | `text-2xl font-semibold` | `--ss-color-text` |

## 3. Motyw ciemny

Każdy token semantyczny ma wartość ciemną (tabela wyżej): tło prawie czarne, karty o stopień
jaśniejsze, ramki zamiast bieli, akcent odwrócony (jasny przycisk z ciemnym tekstem). Motyw
przełączają tokeny (`prefers-color-scheme`, w Tailwind zmienne w `@theme`), więc widoki
**nie** powtarzają klas `dark:`.

## 4. Powierzchnie

- **Karta:** `--ss-color-surface`, ramka 1px `--ss-color-border`, `--ss-radius-card`, bez cienia,
  wewnętrzny odstęp `p-6` (telefon `p-4`).
- **Karta w karcie:** bez ramki, tło `--ss-color-surface-muted`, mniejszy promień.
- **Cień** tylko dla elementów nad płaszczyzną: rozwinięte menu, dropdown, modal, przyklejony
  pasek przy przewijaniu. Karta w siatce nigdy nie ma cienia.
- Tabele tylko dla list wielu rekordów; pojedynczy rekord pokazujemy w kartach (punkt 5).

## 5. Warstwy layoutu

Strona składa się z poziomych warstw, od ogólnej do szczegółowej:

```
┌ Top bar ───────────────────────────────────────────────┐
│ logo        [   wyszukiwarka   ]          ikony  awatar │
├ Nawigacja modułowa ────────────────────────────────────┤
│ ◇ Moduł   (◆ Aktywny)   ◇ Moduł   ◇ Moduł               │
├ Nagłówek kontekstowy ──────────────────────────────────┤
│ ← powrót   metadane · metadane                          │
│ Duży tytuł                                [Eksport PDF] │
├ Stepper ───────────────────────────────────────────────┤
│ ● ─── ● ─── ◉ ─── ○ ─── ○                               │
├ Siatka kart ───────────────────────────────────────────┤
│ ┌ Karta ────────────┐ ┌ Karta ────────────┐            │
│ │ Etykieta  Wartość │ │ Etykieta  Wartość │            │
│ └───────────────────┘ └───────────────────┘            │
└────────────────────────────────────────────────────────┘
```

| Warstwa | Zawartość | Telefon |
| --- | --- | --- |
| **Top bar** | tło `surface`; logo inline SVG (`currentColor`), szeroka wyszukiwarka w pigułce `surface-muted` na środku, ikony akcji, awatar | wyszukiwarka zwija się do ikony |
| **Nawigacja modułowa** | ikona + etykieta (`text_*`); aktywna pozycja = pigułka `surface-muted` i tekst `text`, pozostałe `muted` | przewijanie poziome, bez łamania wiersza |
| **Nagłówek kontekstowy** | link powrotu, metadane (`text-xs`, `muted`, np. numer, data przez `local_date`), duży tytuł, akcje po prawej (np. eksport PDF: etykieta `text_file` / `text_action`); najwyżej jedna akcja główna w kolorze akcentu | akcje pod tytułem |
| **Stepper** | etapy procesu z ikonami połączone cienką linią; stany: zakończony (akcent), bieżący (akcent, wyróżniony), przyszły (`border-strong`). Etapy to przypadki enuma `status` rekordu (zob. [baza-danych.md](baza-danych.md)), etykiety z jego tłumaczeń | pionowo albo tylko bieżący etap z licznikiem „3 z 5” |
| **Siatka kart** | niezależne karty; w karcie pary etykieta (`muted`, lewa) → wartość (`text`, prawa). Etykieta z `text_data($model, $kolumna)`, wartość sformatowana `local_*` | jedna kolumna; para etykieta nad wartością |

## 6. Kolor i dostępność

- Kontrast tekstu co najmniej WCAG AA 4.5:1. `--ss-color-muted` (`#6b7280`) ma 4.8:1 na bieli —
  to najjaśniejszy dopuszczalny szary dla tekstu; jaśniejsze szarości tylko dla ramek i ikon
  dekoracyjnych.
- Akcent wyłącznie dla elementów aktywnych i akcji głównej — jedna akcja główna na widok,
  pozostałe jako przyciski drugorzędne (ramka `border-strong`, tło `surface`).
- Widoczny fokus klawiatury (obwódka w kolorze akcentu) na każdym elemencie interaktywnym.
- Stan nigdy nie jest przekazywany samym kolorem: zawsze tekst lub ikona.

## 7. Komponenty Blade (docelowo)

Wspólne komponenty powstają w Core jako `x-mainboard::…`:

| Komponent | Warstwa |
| --- | --- |
| `page` | szablon bazowy (zaimplementowany): `<head>` z meta `title`, `description`, `keywords`, `tags` (`article:tag`, np. `json_tags` rekordu), `robots`; każdy layout (`auth.layout`, `zone.layout`) składa się z niego |
| `logo`, `styles` | logo SVG inline (`currentColor`) i style Core inline z nonce CSP (zaimplementowane; do czasu builda z §8) |
| `app/welcome` | domyślna strona startowa aplikacji (zaimplementowana): karta z opisem, funkcjami Core jako ikona, tytuł i opis, stopką z logo Laravela i plakietką wersji mainboard (`View\CoreVersion`, plik `VERSION`) oraz panelem marki; aplikacja nadpisuje ją plikiem `resources/views/welcome.blade.php` |
| `BrandNames::highlight()` | wyróżnienie nazw simplysmart i mainboard w tekście (`View\BrandNames`, klasa `.ss-brand-name`): najpierw escapuje, potem pogrubia; w treściach piszemy „mainboard”, nie `simplysmart/mainboard` ani „Core” |
| `icon` | ikona liniowa inline z `public/assets/img/icons/<name>.svg` (zaimplementowany; `stroke="currentColor"`, dekoracyjna `aria-hidden`, klasa `.ss-icon`); nowa ikona to nowy plik SVG 24×24 w tym samym stylu |
| `.ss-badge` | plakietka (klasa CSS do czasu komponentu): pigułka `surface-muted`, drobny tekst, np. wersja |
| `layout` | szkielet strony: top bar, nawigacja, slot treści |
| `top-bar`, `module-nav` | top bar, nawigacja modułowa |
| `context-header` | nagłówek kontekstowy (sloty: metadane, akcje) |
| `stepper` | stepper sterowany enumem statusu |
| `auth.layout` | strony logowania stref (zaimplementowany): karta na środku ekranu z logo, tytułem, wstępem `lead`, komunikatem stanu i slotem `actions` na akcje drugorzędne |
| `card`, `field` | karta i para etykieta–wartość |
| `button` | warianty: główny (akcent), drugorzędny; do czasu komponentu klasy CSS `.ss-button`, `.ss-button--primary`, `.ss-button--block` |

Formularze (do czasu komponentów) składają się z klas statycznego CSS: `.ss-form`, `.ss-field` z
`.ss-field__label` i `.ss-field__error` (z `id` wskazywanym przez `aria-describedby` pola i
`aria-invalid="true"`), `.ss-input` (`.ss-input--code` dla kodu i PIN-u), `.ss-check`, `.ss-link`,
`.ss-notice` (komunikat `role="status"`).
| `flag` | flaga kraju lub języka z `flag_icon` (zaimplementowany; inline SVG 4:3, `.ss-flag`, dekoracyjna) |

Strefy składają z nich swoje widoki. Aplikacja zmienia pojedynczy komponent plikiem w
`resources/views/vendor/mainboard/` (odwrócone nadpisywanie, zob. `CLAUDE.md` szkieletu).
Teksty tylko przez `text_*` / `local_*`.

## 8. Build assetów: Vite w każdej paczce, uruchamiany ze szkieletu (docelowo)

Jedno `npm run dev` / `npm run build` w szkielecie buduje assety szkieletu i wszystkich paczek.
Każda paczka (także mainboard) ma własny Vite, a szkielet tylko je uruchamia.

- **Paczka:** `package.json` (`"private": true`, `"name": "@simplysmart/<paczka>"`, skrypty
  `build` = `vite build`, `dev` = `vite build --watch`), `vite.config.js` i źródła w
  `resources/css/`, `resources/js/`. Tailwind 4 w podejściu CSS-first: `@theme` mapuje tokeny
  `--ss-*` na klasy (`bg-surface`, `text-muted`, `rounded-card`…), `@source` wskazuje widoki
  paczki. Wynik trafia do `public/build/` paczki z własnym `manifest.json` i nie jest commitowany.
- **Szkielet:** jedna, ogólna zmiana — w `package.json` `"workspaces": ["packages/simplysmart/*"]`
  i skrypty `dev`/`build` wołające orkiestrator. `vite.config.js` szkieletu zostaje stockowy.
  Nowa paczka nie wymaga żadnej zmiany w szkielecie.
- **Orkiestrator** (`simplysmart/tools`, skrypt Node `bin/assets.mjs {dev|build}`): znajduje
  paczki z `package.json`. `build` buduje je kolejno, a na końcu szkielet; błąd jednej paczki
  kończy build kodem ≠ 0. `dev` uruchamia równolegle Vite szkieletu i `dev` każdej paczki —
  samo `npm run dev --workspaces` nie wystarczy, bo npm uruchamia workspace'y po kolei, a tryb
  watch się nie kończy.
- **Zależności:** `vite`, `tailwindcss`, `@tailwindcss/vite` w `devDependencies` paczki;
  workspaces instalują je raz, w `node_modules` szkieletu. Nowe zależności wymagają zgody i
  wpisu w [zaleznosci.md](zaleznosci.md).
- **Serwowanie:** komenda Core `php artisan mainboard:assets` tworzy symlink
  `public/vendor/<paczka>` → `public/` paczki w `vendor/simplysmart/<paczka>/` (`--copy` kopiuje,
  gdy hosting nie obsługuje symlinków). `public/vendor/` jest generowane i ignorowane przez git.
- **Widoki:** komponent `x-mainboard::assets package="…" :entries="[…]"` opakowuje
  `Vite::useBuildDirectory('vendor/<paczka>/build')->withEntryPoints(…)`; strefy używają go
  zamiast `@vite`. Hashe w nazwach plików z manifestu zastępują `?v=` z pliku `VERSION`.
- **Tryb dev:** paczki przebudowują się przy zmianie (odświeżenie strony), HMR działa tylko dla
  zasobów szkieletu. HMR paczek (osobny port na paczkę) można dodać później.
- **Wdrożenie:** zob. [wdrozenie.md](wdrozenie.md#assety).
- **Do czasu przejścia** nowe style dopisujemy w obecnym statycznym CSS na tych samych tokenach,
  więc migracja zmieni sposób budowania, nie wygląd.

## 9. Zasady dla kodu i promptów AI

Rób:

- karty: tło `surface`, ramka 1px `border`, `rounded-2xl`, bez cienia;
- etykieta szara i mała, wartość ciemna i wyraźna;
- jedna akcja główna w kolorze akcentu na widok;
- tylko tokeny semantyczne i klasy z `@theme`;
- teksty przez `text_*`, liczby, kwoty i daty przez `local_*`.

Nie rób:

- ciężkich cieni, ostrych narożników, gęstych tabel dla pojedynczego rekordu;
- kolorów spoza tokenów i klas `dark:` w widokach;
- kolorowego logo ani innego koloru akcentu niż grafit;
- literałów tekstu w widokach.
