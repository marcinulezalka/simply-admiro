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
| `--ss-color-accent-text` | fragment w `*gwiazdkach*` (`.ss-accent-text`; strefa może dać kolor marki, akcent przycisków bez zmian) | `var(--ss-color-accent)` | `var(--ss-color-accent)` | `text-gray-800` |
| `--ss-radius-card` | karty, panele, modale | `1rem` | | `rounded-2xl` |
| `--ss-radius-control` | pola, kontrolki | `0.875rem` | | `rounded-xl` |
| `--ss-radius-button` | przyciski, przyciski z ikoną, przełącznik motywu, klawisze PIN (strefa może dać `--ss-radius-pill`) | `var(--ss-radius-control)` | | `rounded-xl` |
| `--ss-radius-input` | pola formularzy `.ss-input` (strefa może dać `--ss-radius-pill`, karty zostają bez zmian) | `var(--ss-radius-control)` | | `rounded-xl` |
| `--ss-radius-pill` | pigułki, awatar, wyszukiwarka | `9999px` | | `rounded-full` |
| `--ss-icon-stroke` | grubość kreski ikon liniowych `.ss-icon` (wygrywa z atrybutem plików; strefa może dać grubszą, miększą kreskę) | `1.75` | | `stroke-[1.75]` |
| `--ss-auth-title-size` | tytuł karty logowania `.ss-auth__title` | `1.5rem` | | `text-2xl` |
| `--ss-auth-header-space` | dodatkowy odstęp między nagłówkiem a formularzem karty logowania (ponad odstęp karty) | `0rem` | | `mb-0` |
| `--ss-auth-legal-border`, `--ss-auth-legal-space` | separator nad odnośnikami prawnymi karty logowania (`.ss-auth__legal`): linia i odstęp nad wierszem; znika z wierszem | `none`, `0rem` | | `border-t`, `pt-6` |
| `--ss-link-decoration` | podkreślenie odnośników `.ss-link` poza najechaniem i fokusem (strefa może dać `none`: podkreślenie tylko pod kursorem) | `underline` | | `underline` |
| `--ss-auth-control-height` | wysokość pól formularza logowania | `2.5rem` | | `min-h-10` |
| `--ss-auth-button-height` | wysokość przycisków formularza logowania (bez `.ss-button--sm`/`--lg`) | `var(--ss-auth-control-height)` | | `h-10` |
| `--ss-shadow-raised` | elementy wyniesione | | | `shadow-sm` |
| `--ss-shadow-overlay` | modal, dropdown | | | `shadow-lg` |
| `--ss-color-canvas` | tło treści powłoki aplikacji (`.ss-shell`), w jasnym motywie jaśniejsze od menu bocznego | `#fbfbfc` | `#0b0d12` | `bg-gray-50` |
| `--ss-color-sidebar` | panel menu bocznego | `#f3f4f6` | `#141821` | `bg-gray-100` |
| `--ss-color-sidebar-hover` | pozycja menu pod kursorem | biel 65% | biel 6% | `bg-white/65` |
| `--ss-shadow-soft` | aktywna pozycja menu, karty i wyszukiwarka w powłoce | | | `shadow-sm` + miękki cień |
| `--ss-color-surface-inverse` | karta odwrócona (wyróżniona kolorem) | `#171717` | `#f3f4f6` | `bg-neutral-900` |
| `--ss-color-on-inverse` | tekst na karcie odwróconej | `#ffffff` | `#111827` | `text-white` |
| `--ss-color-muted-inverse` | etykiety na karcie odwróconej (7,2:1 / 6,9:1) | `#a3a3a3` | `#4b5563` | `text-neutral-400` |
| `--ss-radius-frame` | rama z kartami | `1.75rem` | | `rounded-[28px]` |
| `--ss-frame-padding` | odstęp ramy i między kartami w ramie | `0.75rem` | | `p-3` |
| `--ss-radius-nested` | karta w ramie: promień ramy minus odstęp | `1rem` | | `rounded-2xl` |
| `--ss-shadow-frame` | miękki cień ramy | | | `shadow-sm` + rozmyty |
| `--ss-shadow-elevated` | jedna wyróżniona, wyniesiona karta widoku | | | `shadow-2xl` |

- **Logo Laravela** (`public/assets/img/vendor/laravel.svg`, z oficjalnego repozytorium
  `laravel/art`, znak towarowy Laravel Holdings) występuje tylko jako informacja „Zbudowane na”,
  monochromatycznie przez `currentColor`, nigdy w czerwieni ani jako element marki simplysmart.
- **Akcent to grafit**, spójny z czarno-białą marką. Logo zostaje wyłącznie czarne albo białe
  (zob. [brand/README.md](../brand/README.md)).
- Kolory stanów (sukces, ostrzeżenie, błąd, informacja) dostają osobne tokeny semantyczne
  przy pierwszym komponencie, który ich potrzebuje; zawsze z tekstem lub ikoną, nie samym kolorem.
  Tokeny stanów (tekst/ikona i tło; kontrast co najmniej 4,8:1 w obu motywach):

  | Stan | Znaczenie | Tekst / ikona (jasny · ciemny) | Tło (jasny · ciemny) |
  | --- | --- | --- | --- |
  | `success` | akcja się udała | `#15803d` · `#4ade80` | `#f0fdf4` · `#052e16` |
  | `info` | informacja, bez potrzeby działania | `#1d4ed8` · `#60a5fa` | `#eff6ff` · `#172554` |
  | `warning` | wymaga uwagi, akcja wykonana | `#92400e` · `#fbbf24` | `#fffbeb` · `#451a03` |
  | `danger` | błąd: akcja się nie udała (też błędy pól) | `#b91c1c` · `#f87171` | `#fef2f2` · `#450a0a` |

  Tokeny: `--ss-color-<stan>` i `--ss-color-<stan>-surface`.
- Logo i panel marki używają `--ss-color-brand` / `--ss-color-on-brand` (czerń albo biel), nie
  `--ss-color-text`, bo grafitowy tekst nie jest kolorem marki.
- Typografia: `--ss-font-sans` (font interfejsu: przyciski, pola, menu boczne, zakładki, tabele, odznaki, etykiety,
  przełączniki, toasty; font systemowy, dopóki marka nie ma własnego), `--ss-font-base` (treść strony, `body`;
  domyślnie `--ss-font-sans`, np. szeryfowy font treści przy bezszeryfowych kontrolkach), `--ss-font-display` (tytuł
  logowania, domyślnie `--ss-font-sans`) i `--ss-font-accent` (fragment w `*gwiazdkach*`, domyślnie font otoczenia);
  strefa podmienia je w swoim arkuszu fontami z `fonts` w configu strefy. Skala:

| Element | Tailwind | Kolor |
| --- | --- | --- |
| Metadane nad tytułem, etykiety pól | `text-xs` / `text-sm` | `--ss-color-muted` |
| Wartości, treść | `text-sm` / `text-base` | `--ss-color-text` |
| Tytuł karty | `text-base font-semibold` | `--ss-color-text` |
| Tytuł strony (nagłówek kontekstowy) | `text-2xl font-semibold` | `--ss-color-text` |

## 3. Motyw ciemny

Każdy token semantyczny ma wartość ciemną (tabela wyżej): tło prawie czarne, karty o stopień
jaśniejsze, ramki zamiast bieli, akcent odwrócony (jasny przycisk z ciemnym tekstem). Widoki
**nie** powtarzają klas `dark:` — motyw przełączają wyłącznie tokeny.

- **Tokeny:** wartość z wariantem ciemnym to `light-dark(jasny, ciemny)` w jednej linii (w cieniu tylko kolor cienia).
  O wariancie decyduje `color-scheme`: domyślnie motyw systemu, a wybór użytkownika ustawia `data-theme="light|dark"`
  na `<html>` (`:root[data-theme=…] { color-scheme: … }` w `tokens.css`). W Tailwind te same tokeny trafią do `@theme`.
- **Przełącznik** `x-admiro::theme-toggle`: przełącznik segmentowy z czterema motywami (`Enums\Theme`) — jasny,
  ciemny, kontrast WCAG (`contrast`: czerń na bieli) i duży kontrast WCAG (`contrast-yellow`: żółty na czarnym); wybrany
  segment ma `aria-pressed="true"`, etykiety w `title` i dla czytników ekranu. Stoi w ustawieniach konta (strona
  „Wygląd”, `account.appearance`; menu boczne aplikacji i strony logowania go nie mają), na stronach treści i na stronie design systemu. Wybór zapisuje przeglądarka (`localStorage`), a `js/theme.js` w `<head>` każdej strony
  (`x-admiro::page`) stosuje go przed narysowaniem (bez błysku). Bez skryptu przełącznik jest ukryty i obowiązuje
  motyw systemu.
- **Motywy wysokiego kontrastu (WCAG 2.2 AAA):** bloki `:root[data-theme="contrast"]` i `[data-theme="contrast-yellow"]`
  na końcu `tokens.css` podmieniają wszystkie tokeny semantyczne na wartości o kontraście co najmniej 7:1 (ramki pełne,
  stany w odróżnialnych kolorach z tekstem lub ikoną). Stoją po kolorystykach, więc `data-palette` w nich nie działa.
  Strona design systemu pokazuje w tabeli tylko wartości jasne i ciemne.
- **Wysoki kontrast** (motywy `contrast`, `contrast-yellow`): poza tokenami odnośniki są podkreślone, fokus to gruby
  dwukolorowy pierścień (kolor tekstu z obwódką w kolorze tła), a karty, pola i przyciski mają pełne obramowanie zamiast
  cieni; bez wyboru system z `prefers-contrast: more` dostaje kontrast czarno-biały.
- **Elementy tylko dla jednego motywu** (np. dwa obrazy banera): klasy `.ss-theme-light` / `.ss-theme-dark`, zgodne
  z wyborem i z motywem systemu (kontrast czarno-biały liczy się jako jasny, żółto-czarny jako ciemny).
- **Kolorystyka strefy (warstwa bazowa):** arkusz strefy (`stylesheets`) nadpisuje w `:root` tylko tokeny semantyczne
  (`light-dark()` dla obu motywów), np. tło, powierzchnie, tekst i akcent bazowy `--ss-color-accent-base`
  (`--ss-color-accent` jest z niego liczony) — nigdy klas komponentów. Kontrast tekstu co najmniej 4,5:1.
- **Kolorystyki do wyboru (warstwa nakładana):** `Enums\Palette` (teal, indigo, blue, emerald, violet) z tokenami
  `--ss-palette-<paleta>-{accent,accent-hover,info,info-surface,inverse}` w `tokens.css`. Atrybut `data-palette` na
  `<html>` (`:root[data-palette=…]`, wygrywa z `:root` arkusza strefy) podmienia akcent, informację i kartę odwróconą
  w obu motywach; powierzchnie zostają ze strefy. Strefa wybiera oferowane kolorystyki w configu (`palettes`);
  `x-admiro::palette-switch` pokazuje „Domyślna” (kolory bazowe) i listę z próbkami; layouty (aplikacja, logowanie)
  go nie wyświetlają — przełącznik jest na stronie „Wygląd” w ustawieniach konta (strefa z `palettes`) i w sekcji
  „Kolorystyki” design systemu, a każda strona stosuje zapisany wybór. `x-admiro::page` dostaje te same `palettes` i `paletteScope`
  (meta `ss-palettes` przed `js/theme.js`), więc wybór — zapamiętany w przeglądarce osobno dla każdej strefy — działa
  bez błysku; kolorystyka spoza listy strefy jest pomijana. Bez skryptu przełącznik jest ukryty.

## 4. Powierzchnie

- **Karta:** `--ss-color-surface`, ramka 1px `--ss-color-border`, `--ss-radius-card`, bez cienia,
  wewnętrzny odstęp `p-6` (telefon `p-4`).
- **Karta w karcie:** bez ramki, tło `--ss-color-surface-muted`, mniejszy promień.
- **Cień** tylko dla elementów nad płaszczyzną: rozwinięte menu, dropdown, modal, przyklejony
  pasek przy przewijaniu. Karta w siatce nigdy nie ma cienia.
- **Rama z kartami** (`.ss-frame`, `.ss-frame--row`): kilka kart obok siebie na wspólnej ramie (tło
  `surface`, odstęp `--ss-frame-padding`, promień `--ss-radius-frame`, cień `--ss-shadow-frame`). Karty
  w ramie mają jednolite tło i różnią się kolorem (`.ss-card--tinted` jasna, `.ss-card--inverse`
  odwrócona) oraz promień zagnieżdżony `--ss-radius-nested` (rama minus odstęp: krawędzie równoległe).
  Jedna wyróżniona karta widoku może być wyniesiona (`.ss-card--elevated`, `--ss-shadow-elevated`) —
  jedyny wyjątek od „karta bez cienia”.
- **Przyciski z ikonami**: `x-admiro::icon` z klasą `.ss-button__icon` przed tekstem (akcja, np. odśwież)
  albo po nim (przejście dalej, strzałka); `.ss-button--pill` dla akcji w ramie. Na karcie odwróconej akcja
  główna odwraca kolory sama.
- **Rozmiary przycisków** (tokeny `--ss-button-height`, `-padding`, `-icon`, `-gap`, `-font` ustawiane przez
  modyfikator): `.ss-button--sm` — 36px wysokości, wcięcie 12px, ikona 16px, odstęp ikony 6px, tekst 14px (tabele,
  karty); domyślny średni — 40px, 16px, ikona 20px, odstęp 8px, tekst 16px; `.ss-button--lg` — 56px, 20px, ikona
  24px, odstęp 8px, tekst 18px (jedna duża akcja ekranu). Pigułka (`--pill`) ma te same wymiary.
- **Panel przycisków (przełącznik segmentowy)** `.ss-tabs` — wybór jednej opcji (motyw, zakres okresu, dostępność, sekcja); segmenty jako odnośniki (`aria-current`) albo przyciski (`button.ss-tabs__link`, `aria-pressed`), ikona `.ss-tabs__icon`; sekcja „Panele przycisków” w design systemie. Dwa rodzaje: klasyczny `.ss-tabs--classic` — pojedyncza ramka, tło powierzchni, płaski wybrany segment (jak przełącznik motywu); wypukły (domyślny): pigułka z podwójnym obrysem — wewnętrzna
  ramka i zewnętrzny pierścień oddzielony tłem powierzchni (`--ss-segment-ring`, jasny odcień akcentu;
  `--ss-segment-stroke`, `--ss-segment-gap`), tło przygaszone; wybrany segment w akcencie, przestrzenny
  (`--ss-shadow-segment`: cienie wewnętrzne — ciemniejszy u góry, jasny i w kolorze akcentu u dołu — i poświata
  akcentu pod spodem); w motywach wysokiego kontrastu bez cieni, pierścień w kolorze ramki. Wygląd czytany z tokenów na panelu (`--ss-segment-bg`, `--ss-segment-halo`, `--ss-segment-margin` i powyższe), więc arkusz strefy daje jeden rodzaj wszystkim panelom. Plakietka może mieć ikonę `.ss-badge__icon` (np. stan „bezpieczne”).
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

Wspólne komponenty powstają w Core jako `x-admiro::…`:

| Komponent | Warstwa |
| --- | --- |
| `page` | szablon bazowy (zaimplementowany): `<head>` z meta `title`, `description`, `keywords`, `tags` (`article:tag`, np. `json_tags` rekordu), `robots`; każdy layout (`auth.layout`, `zone.layout`) składa się z niego |
| `logo`, `styles` | logo SVG inline (`currentColor`) i style Core inline z nonce CSP (zaimplementowane; do czasu builda z §8) |
| `app/welcome` | domyślna strona startowa aplikacji (zaimplementowana): rama `.ss-frame` z dwiema kartami o zagnieżdżonych zaokrągleniach — karta z opisem, funkcjami Core jako ikona, tytuł i opis, stopką z logo Laravela i plakietką wersji admiro (`View\CoreVersion`, plik `VERSION`) oraz kartą marki (logo simplysmart na środku — odnośnik do `admiro.welcome.website`, `WELCOME_WEBSITE`, w nowej karcie — pod nim kafelki z bardzo delikatnym gradientem i przygaszonym logo zarejestrowanych stref, `View\ZoneLogos`, `.ss-welcome__zones`; logo strefy z `website` prowadzi do jej strony w nowej karcie); aplikacja nadpisuje ją plikiem `resources/views/welcome.blade.php` |
| `BrandNames::highlight()` | wyróżnienie nazw simplysmart i admiro w tekście (`View\BrandNames`, klasa `.ss-brand-name`): najpierw escapuje, potem pogrubia; `BrandNames::withLogo()` zamiast nazwy simplysmart wstawia logo SVG w linii tekstu (`.ss-logo-inline`, nagłówek strony startowej); w treściach piszemy „admiro”, nie `simplysmart/admiro` ani „Core” |
| `icon` | ikona liniowa inline z `public/assets/img/icons/<name>.svg` (zaimplementowany; `stroke="currentColor"`, dekoracyjna `aria-hidden`, klasa `.ss-icon`); rysunki z zestawu [Lucide](https://lucide.dev) (ISC, zaokrąglone końce i łączenia, kreska 1,75 we wspólnym nagłówku Core, w CSS z tokenu `--ss-icon-stroke`; źródło i licencja w `docs/zaleznosci.md`); nowa ikona to plik SVG 24×24 z Lucide z tym samym nagłówkiem |
| `.ss-badge` | plakietka (klasa CSS do czasu komponentu): pigułka `surface-muted`, drobny tekst, np. wersja |
| `layout` | szkielet strony: top bar, nawigacja, slot treści |
| `top-bar`, `module-nav` | top bar, nawigacja modułowa |
| `context-header` | nagłówek kontekstowy (sloty: metadane, akcje) |
| `stepper` | stepper sterowany enumem statusu |
| `auth.layout` | strony logowania stref (zaimplementowany): tytuł, wstęp `lead`, komunikat stanu, slot `actions` na akcje drugorzędne, pod nimi przycisk „Strona główna” (`.ss-auth__home`, przycisk z ikoną domu do `url('/')`, jak na stronach błędów; strefa wyłącza go `auth.home_link` = false); pola i przyciski mają wysokość `--ss-auth-control-height` (pole bez pionowego wcięcia, więc wysokość daje token); dwa układy (`Enums\AuthLayout`, `auth.layout` strefy, prop `layout`): `centered` — jedna karta z logo, `split` — rama `.ss-frame` z kartą marki (`.ss-card--inverse`, `.ss-card--elevated`; logo simplysmart, logo SVG strefy z `auth.logo` albo baner strefy z `auth.brand` jako `<picture>` z wersją dla ciemnego motywu, `.ss-auth__brand--image`) po lewej i kartą formularza (`.ss-card--tinted`) po prawej (na telefonie i tablecie jedna pod drugą, karta formularza na całą szerokość ramy), `split-reversed` — to samo w odbiciu lustrzanym (`.ss-auth--reversed`, od 56rem `row-reverse`: formularz po lewej, marka po prawej; na telefonie marka nad formularzem); z banerem (`.ss-auth--image`) karta banera nie ma ramki ani tła, na szerokim ekranie jej wysokość wynika z proporcji obrazu (baner bez obcinania, karta formularza dopasowuje się do niego), a karta formularza nachodzi na nią o `--ss-radius-nested` i zakrywa jej zaokrąglenie (arkusz strefy może to zmienić); tło strony to delikatny gradient (`--ss-color-auth-from` → `--ss-color-auth-to`, odrobina akcentu w rogu) w każdym motywie; bez przełącznika motywu (obowiązuje motyw systemu albo zapisany wybór); na dole karty formularza drobny wiersz: odnośniki prawne strefy (`legal_links`), polityka cookies i ustawienia cookies (`.ss-auth__legal`; cookies tylko z opublikowaną polityką albo skryptami zgody) — logowanie nie ma stopki strony simplysmart; wygląd wyłącznie z klas design systemu |
| `content.layout` | strona treści (zaimplementowany; np. polityka prywatności): pasek `content.bar` z powrotem (`back`) i odnośnikami cookies nad i pod treścią (bez przełącznika motywu; odnośniki `.ss-link` bez podkreślenia, podkreślone pod kursorem), spis treści `.ss-toc` z `toc` (`list<array{id, label}>`, na szerokim ekranie przyklejony po lewej), karta dokumentu z nagłówkiem (`eyebrow`, tytuł z opcjonalną ikoną `icon`, `lead`) i treścią w `.ss-prose` (tekst do 70ch, bloki na całą szerokość; odnośniki pogrubione w kolorze akcentu, podkreślone dopiero pod kursorem i przy fokusie); na stronie polityki cookies lista cookies `.ss-cookie-list` jako panele `.ss-card--muted` na całą szerokość z zawijaniem długich wartości i przycisk ustawień z ikoną, opcjonalna karta „Zobacz także” (slot `related`, odnośniki `.ss-related`) |
| `card`, `field` | karta i para etykieta–wartość |
| `button` | warianty: główny (akcent), drugorzędny; do czasu komponentu klasy CSS `.ss-button`, `.ss-button--primary`, `.ss-button--block` |

Formularze (do czasu komponentów) składają się z klas statycznego CSS: `.ss-form`, `.ss-field` z
`.ss-field__label` i `.ss-field__error` (z `id` wskazywanym przez `aria-describedby` pola i
`aria-invalid="true"`), `.ss-input` (`.ss-input--code` dla kodu i PIN-u), `.ss-link`,
`.ss-notice` (komunikat `role="status"`).
| `flag` | flaga kraju lub języka z `flag_icon` (zaimplementowany; inline SVG 4:3, `.ss-flag`, dekoracyjna) |

Strefy składają z nich swoje widoki. Aplikacja zmienia pojedynczy komponent plikiem w
`resources/views/vendor/admiro/` (odwrócone nadpisywanie, zob. `CLAUDE.md` szkieletu).
Teksty tylko przez `text_*` / `local_*`.

### Strona design systemu

`/design-system` (trasa `simplysmart.design-system`, przy kilku językach `/{locale}/design-system`) pokazuje na
żywo tokeny i komponenty: kolory (jasny i ciemny), typografię, promienie, cienie, odstępy, przyciski,
formularze, komunikaty, karty, ikony i markę. Tokeny czyta `View\DesignTokens` wprost z `tokens.css`
(rozwiązuje `var()` dla obu motywów), ikony to pliki z `img/icons/`, więc nowy token lub ikona pojawia się na
stronie bez zmian w widoku; nowy komponent dopisujemy do `resources/views/design-system/index.blade.php`.
Domyślnie działa tylko poza produkcją (`DESIGN_SYSTEM_PAGE`, sprawdzane przy żądaniu; wyłączona = 404) i nie
jest indeksowana.

Nawigacja (komponent `x-admiro::design-system.nav`, karta po lewej) ma dwie grupy: **Fundamenty** (sekcje
strony) i **Widoki** — gotowe ekrany aplikacji pod `/design-system/views/{widok}` (`simplysmart.design-system.view`),
pogrupowane w rozwijane obszary (`Enums\DesignSystemArea`, natywne `<details>`, obszar bieżącego widoku
rozwinięty): **Autoryzacja** (logowanie klasyczne i na dwóch kartach, kod z e-maila, błędny kod, PIN, PIN z otwartą
klawiaturą ekranową, zbyt wiele prób, ustawienie i potwierdzenie PIN-u, wygasła sesja logowania, „Nie pamiętam PIN-u”
oraz jego warianty: rozwinięty zapasowy adres i oczekujący kod resetu w polach cyfr), **Panel aplikacji** (pulpit,
lista rekordów, szczegóły rekordu, formularz, menu boczne), **Strony treści** (polityka prywatności na `content.layout`) i **Błędy** (401, 403, 404, 419, 429, 500, 503). Ekrany
logowania i strony błędów to podglądy prawdziwych plików produkcyjnych (`admiro::auth.*`, `app/errors/*`):
logowanie na pierwszej strefie z logowaniem, z jej ustawieniami, błędami pól i stanami wariantów (`keypadOpen`,
`recoveryOpen`, `codePending`; `View\DesignSystem\SignInPreviews`).

### Pola, maski i awatary

- **Pole** `x-admiro::input`: etykieta nad polem, subtelna ikona w tle po lewej (przygaszona, pełna przy
  fokusie), podpowiedź i błąd powiązane przez `aria-describedby` (`aria-invalid` przy błędzie). Popularne pola
  mają ikonę: e-mail `mail`, osoba `user`, firma `building`, adres `map-pin`, data `calendar`.
- **Podpowiedzi w polach** (placeholder): sam przykład formatu, bez „np.” (np. `jan.kowalski@przyklad.pl`), na neutralnych, fikcyjnych danych, nigdy zamiast
  etykiety ani instrukcji (znika przy pisaniu); kolor `--ss-color-muted` z pełnym kryciem (kontrast 4,8:1). Wspólne
  teksty w grupie `placeholders.*` (e-mail, imię, nazwisko, imię i nazwisko, firma, adres, wyszukiwanie, notatka) i wzór
  `placeholders.example`; maski biorą go przez `InputMask::placeholder()`. Pola kodu i PIN-u nie mają podpowiedzi.
- **Pole PIN-u** `x-admiro::pin-field` (kroki logowania z PIN-em, ustawienie PIN-u, zmiana PIN-u w bezpieczeństwie):
  `.ss-input--code` (cyfry rozstrzelone, w rozmiarze tekstu pól) z opcjonalną subtelną ikoną po lewej (prop `icon`;
  w logowaniu `lock`) i przyciskiem klawiatury ekranowej po prawej (`.ss-input-wrap--keypad`, ikona `keypad`), który otwiera
  modal `.ss-keypad` (natywny `<dialog>`, `js/pin-keypad.js`) w kolorach karty formularza logowania (tło surface-muted,
  ramka border-strong): nazwa pola, kropki wpisanych cyfr z zamknięciem obok (`.ss-keypad__bar`), smukłe, małe klawisze
  w wysokości przycisków z ikoną (40 px) (1–9, „C” czyści, „0” na dwie kolumny) i stopka z ikoną `backspace` (usuwa ostatnią cyfrę) obok
  „Zatwierdź PIN” z ikoną `check`, które zamyka okno i od razu wysyła formularz (`requestSubmit`), a gdy formularz nie
  jest jeszcze poprawny, przenosi fokus do pierwszego niepoprawnego pola (np. powtórzenia PIN-u). Kropki pokazują liczbę
  wpisanych cyfr (liczba także dla czytników ekranu), cyfry trafiają do pola na bieżąco (do `maxlength`), fizyczna
  klawiatura działa w polu i w oknie (Enter = „Zatwierdź PIN”). Bez skryptu przycisk jest ukryty.
- **Pole kodu z e-maila** `x-admiro::code-field` (krok kodu logowania, kod resetu PIN-u): osobne pole na każdą cyfrę
  (`.ss-code`, `.ss-code__cell`, `js/code-field.js`); cyfra przechodzi do następnej komórki, Backspace w pustej wraca,
  strzałki przesuwają, wklejenie i autouzupełnianie rozkładają cały kod. Kod trafia do ukrytego pola `name`; bez skryptu
  zostaje jedno zwykłe pole `.ss-input--code` z ikoną `shield-check`.
- **Ekran „Nie pamiętam PIN-u”** (`.ss-forgot`): albo/albo — nagłówek-odnośnik „Masz zapasowy adres e-mail?” (`<details>`)
  rozwija formularz zapasowego adresu i ukrywa kroki bez zapasowego adresu, zwinięcie przywraca kroki; otwarty od razu,
  gdy czeka kod albo są błędy. Przyciski standardowe, „Wróć do logowania” drugorzędny.
- **Maski** `Enums\InputMask` (telefon, kod pocztowy, NIP, PESEL, numer rachunku): wzór (`9` cyfra, `A` litera),
  klawiatura numeryczna, ikona, przykład w placeholderze i format w podpowiedzi. `js/input-mask.js` formatuje
  w trakcie pisania i przy wklejaniu; bez skryptu pole przyjmuje tekst, a format podaje podpowiedź.
- **Awatar** `x-admiro::avatar`: zdjęcie albo inicjały (`View\Initials`: pierwsza i ostatnia część zaczynająca
  się wielką literą, bez „dr” itp.), rozmiary `sm`, `md`, `lg`, `accent` dla bieżącego użytkownika. Jest
  dekoracyjny — nazwę podaje tekst obok (`.ss-person`). Pokazujemy go wszędzie, gdzie prezentujemy osoby.
- **Kolor awatara** (`Enums\AvatarColor`, tokeny `--ss-avatar-<kolor>-bg` / `-fg`, kontrast co najmniej 6,4:1):
  nowe konto dostaje kolor z adresu e-mail (`json_attributes.avatar_color`), użytkownik zmienia go w profilu
  (`x-admiro::avatar-colors`); awatar przyjmuje `color`, a ta sama osoba ma ten sam kolor na każdym ekranie.
- **Menu boczne**: u góry awatar z imieniem i nazwiskiem (albo nazwą) i drugą linią (organizacja, rola) jako
  menu konta, pod nim akcje (zwijanie, przełącznik kontekstu `contexts` — np. organizacja albo widok pracy, bieżący
  z haczykiem — i powiadomienia), na dole subtelne logo (3rem).

### Okresy i kalendarz

- **Nagłówek okresu** `.ss-period`: zakładki zakresu (`.ss-tabs`, np. dzień / tydzień / miesiąc), przyciski
  poprzedni i następny (`.ss-icon-button` z nazwą dla czytników), nazwa okresu (`aria-live="polite"`) i „Dziś”.
- **Kalendarz miesiąca** `.ss-calendar` (tydzień od poniedziałku, nazwy dni z `local_day`): dzień z numerem,
  treścią (np. liczba zamówień) i wskaźnikiem; dziś `aria-current="date"` z obwódką akcentu; dni spoza miesiąca
  przygaszone; nieczynne z opisem tekstowym; święto / dzień wolny `.ss-calendar__day--holiday` (tło ostrzeżenia)
  z nazwą `.ss-calendar__holiday` — dni wolne daje `Queries\Dictionaries\HolidaysIn` (w legendzie próbka tej klasy).
- **Dostępność w siatce**: komórki w godzinach dostępności `.ss-cell--open` (bardzo lekkie tło akcentu), poza nimi
  `.ss-cell--closed` (subtelne ukośne kreskowanie); zawsze z legendą `.ss-legend` i opisem tekstowym w pustej
  komórce dla czytników ekranu.
- **Wskaźnik obłożenia** `.ss-meter` na natywnym `<meter>` (`low`, `high`, `optimum="0"`): kolor akcentu,
  ostrzeżenia albo błędu, zawsze z wartością liczbową obok w tekście.

### Toasty i powiadomienia

- **Toasty** (`x-admiro::toasts`, typy `Enums\ToastType`, obiekt `View\Toast`): alerty po akcji na dole
  ekranu, wyśrodkowane. Kolor mówi, czego dotyczy komunikat (tabela stanów wyżej), a zawsze towarzyszą mu ikona typu i nazwa
  (tytuł albo prefiks dla czytników). Błąd (`danger`) ma `role="alert"` i zostaje do zamknięcia; pozostałe mają
  `role="status"` i znikają po 6 s (`js/toasts.js`; pauza przy najechaniu i fokusie). Toast może mieć jedną
  akcję (link, np. „Zobacz”, „Cofnij”). Layouty aplikacji, logowania i strony publicznej strefy pokazują toasty
  z sesji (`Toast::fromFlash()`): akcja flashuje `Toast::SESSION_KEY` z listą `Toast::toArray()`, a krótki komunikat
  `status` (`Toast::STATUS_KEY`, np. „Wylogowano” po wylogowaniu) staje się toastem powodzenia — komunikaty z sesji
  nie trafiają do szarego `.ss-notice` w karcie. Wariant `inline` to próbki w treści strony.
- **Powiadomienia** (`View\Notifications\NotificationPanel`, `NotificationItem`, `x-admiro::notification`):
  dzwonek pod nazwą konta w menu bocznym z licznikiem nieprzeczytanych (liczba także w tekście dla czytników) i panelem
  (`<details data-ss-dropdown>`, jak menu konta: otwarcie jednego zamyka drugie); pełna lista na osobnej stronie.
- **Switch zamiast checkboxa**: w formularzach każde pole tak/nie to `x-admiro::switch` (także „Zapamiętaj
  to urządzenie” przy logowaniu); `name` i `value` zostają jak w checkboxie. Wybór jednej z kilku opcji to
  `.ss-choices`.

### Zgoda na cookies

- **Baner i ustawienia** `x-admiro::cookie-consent` (w szablonie bazowym `x-admiro::page`, więc na każdej
  stronie każdej strefy): natywne `<dialog>` otwierane modalnie (fokus w oknie, tło `::backdrop`, Esc), oba okna
  tej samej szerokości (do 48rem); przyciski każdego okna (`.ss-consent__actions`) w jednym wierszu, po równo,
  a na wąskim telefonie (poniżej 36rem) jeden pod drugim.
  - Baner `.ss-consent__banner`: tytuł, opis, trzy przyciski — „Zezwól na wszystkie” (główny), „Odrzuć”
    i „Dostosuj” (drugorzędne, obrys) — i odnośnik do polityki.
  - Okno ustawień `.ss-consent__settings`: kategorie (`Enums\CookieCategory`) jako `x-admiro::switch`
    (niezbędne zablokowane, „Zawsze włączone”), lista cookies kategorii w `<details>`, przyciski i zamknięcie
    `.ss-icon-button`; zamknięcie bez decyzji przywraca baner.
  - Logika w `js/cookie-consent.js` (inline z nonce CSP): ciasteczko zgody, rejestr zgód, skrypty opcjonalne
    `<script type="text/plain" data-ss-consent-script>` uruchamiane po zgodzie.
- **Odnośniki** `x-admiro::cookie-links` („Ustawienia cookies” — każdy element z `data-ss-cookie-settings`
  otwiera okno; „Polityka cookies”) w stopce logowania, w pasku stron treści i w menu konta layoutu aplikacji.
- Baner pojawia się tylko, gdy opublikowano politykę cookies albo paczka zarejestrowała skrypt opcjonalny.

### Logowanie

- Nagłówek karty logowania (`x-admiro::auth.layout`, prop `title`) może mieć wyróżniony fragment w `*gwiazdkach*`:
  `View\AccentText` zamienia go na `.ss-accent-text` (kursywa w kolorze akcentu), a w `<title>` zostaje sam tekst.
- Notka pod formularzem (prop `note`, `.ss-auth__note`): mniejszy, przygaszony tekst, np. o blokadzie po błędnych
  PIN-ach. Teksty kroków podmienia paczka strefy (`docs/strefy.md`).

### E-maile

- E-maile stref (`Mail\ZoneMailable`, `docs/strefy.md` „E-maile strefy”) mają ramę `admiro::mail.frame`: tabele
  600 px, logo osadzone w wiadomości, biała karta treści z ramką i zaokrągleniem, stopka (informacja o wysyłce
  automatycznej, odnośniki prawne strefy, copyright). Style inline w kolorach `mail.palette` strefy — domyślnie
  wartości z palety design systemu (tło slate-50, karta biała, ramka gray-200, tekst gray-900, przytłumiony gray-500,
  akcent gray-800), bo klienci poczty nie czytają tokenów `--ss-*`.
- Kod logowania jest wyróżniony (32 px, krój o stałej szerokości), a akcja (np. „Zaufane urządzenia”) to przycisk
  w kolorze akcentu. Logo tylko PNG/JPG.
- Podgląd pliku marki w formularzu (logo, sygnet, ikona strony organizacji w panelu): `.ss-brand-preview`.

### Strony błędów

`resources/views/app/errors/<kod>.blade.php` (komponent `x-admiro::error.layout`, teksty `errors.*`): rama
z kartą marki z dużym kodem i kartą z tytułem, opisem i akcjami (strona główna, powrót; dla 500 i 503 „Spróbuj
ponownie”). Katalog `resources/views/app/` jest ostatnią ścieżką `view.paths`, więc Laravel sam używa tych stron,
a aplikacja nadpisuje pojedynczą plikiem `resources/views/errors/<kod>.blade.php`. Bez sesji, bazy i skryptów;
w produkcji maskowanie błędów pokazuje 404 zamiast 401/403/405/419/429. Lista widoków to enum `Enums\DesignSystemView` (nowy widok = nowy
przypadek, jego obszar w `area()` i plik w `resources/views/design-system/views/`). Nawigacja i trasa czytają
**katalog** `Contracts\DesignSystemCatalog` (singleton, `Providers\DesignSystemServiceProvider` wpisuje do niego
widoki Core): paczka strefy dokłada własne obszary i widoki w swoim providerze
(`callAfterResolving(DesignSystemCatalog::class, …)`, `addArea()`, `addView()` z leniwymi danymi), bez zmian
w Core — np. makiety swoich ekranów; klucze są unikalne w aplikacji. Widoki na pełnym layoucie aplikacji mają nad
sobą pasek `x-admiro::design-system.strip` (powrót i widoki tego samego obszaru), powłoka aplikacji to
`x-admiro::design-system.shell`, a przykładowe dane (faktury, status jako enum sterujący stepperem) jedno
źródło `View\Demo\DemoInvoices`.

**Layout aplikacji z menu bocznym** `x-admiro::app.layout`: menu po lewej (`View\Navigation\NavigationGroup`
z `NavigationItem`: etykieta, adres, ikona, bieżąca pozycja przez `aria-current`, licznik, podpozycje `children` —
pozycja z nimi jest gałęzią `.ss-sidebar__tree` (`<details>` z chevronem, rozwinięta, gdy ona albo podpozycja jest
bieżąca), a podpozycje bez ikon (`.ss-sidebar__sublist`) łączą linie drzewa spod ikony pozycji z zaokrąglonym łukiem
do każdej (`--ss-sidebar-tree-line`); w menu zwiniętym do ikon gałąź jest zwykłym odnośnikiem pozycji; obszary widoków
w nawigacji design systemu mają te same linie), zwijane do ikon
i rozwijane przyciskiem w top barze (stan w `localStorage`), na telefonie wysuwane z boku (tło i Esc zamykają);
globalna wyszukiwarka `View\Navigation\SearchBox` na środku top baru (formularz GET, skrót „/”; opcjonalnie zakresy
„Szukaj w” — `SearchScope`, checkboksy `in[]` w menu przy polu — i podpowiedzi w trakcie pisania z `suggestUrl`:
fragment HTML z widoku `admiro::search.suggestions` z grupami `SearchResultGroup` i wynikami `SearchResult` z flagami
albo ikoną, lista `role="listbox"` obsługiwana strzałkami, Enter i Esc, `js/global-search.js`); formularze
`data-ss-autosubmit` wysyłają się po zmianie pola, np. przełącznik włącz/wyłącz w wierszu tabeli (`js/autosubmit.js`,
bez skryptu zostaje przycisk `data-ss-autosubmit-fallback`); tło treści powłoki (`--ss-color-canvas`) jest w jasnym
motywie jaśniejsze od menu bocznego; konto z menu pod awatarem (`account.items`, `<details>`, działa bez skryptu; skrypt zamyka je kliknięciem obok
i Esc); nagłówek kontekstowy
`x-admiro::app.context` (też w powłoce widoków design systemu). Menu ma najwyżej `--ss-sidebar-width`, a dłuższe
pozycje, imię i druga linia zawijają się do kolejnych wierszy (bez wielokropka). Każda grupa pozycji to osobna lista
(`.ss-sidebar__section`: nazwa grupy i pozycje w zaokrąglonym bloku z cienką ramką na jaśniejszym tle
`--ss-sidebar-section`, bieżąca pozycja na tle `surface-muted`), grupy dzielą większe odstępy zamiast kresek. Na szerokim ekranie menu przylega
do krawędzi okna (bez marginesów i zaokrągleń, ramka z prawej). Pod nazwą konta ikony: zwijanie menu, przełącznik
kontekstu, ustawienia (ikona suwaków `.ss-sidebar__settings-toggle`, `settings`: `View\Navigation\SettingsMenu`; na
stronach ustawień wypełniona akcentem) i dzwonek powiadomień. Na dole menu przycisk „Wyloguj” (`signOut`, formularz POST
`.ss-sidebar__sign-out`) i subtelne, wyśrodkowane logo simplysmart (35% szerokości menu,
`.ss-sidebar__brand-toggle`), które odsłania pełnoszeroką stopkę strony simplysmart (`x-admiro::brand-footer`,
`.ss-footer`: odnośniki prawne strefy `legalLinks`, cookies, copyright, przełącznik motywu, duże logo): powłoka `.ss-shell`
przesuwa się w górę nad stopką, dłuższa strona najpierw płynnie przewija się na dół, a przewinięcie w górę chowa stopkę;
bez stałego menu (poniżej 64rem) stopka jest na końcu strony. Na stronie ustawień (`settings->active`)
menu pokazuje zamiast pozycji aplikacji „Wróć do aplikacji”, tytuł „Ustawienia” i grupy ustawień (`docs/strefy.md`). Skrypt `public/assets/js/app-shell.js`
osadza `x-admiro::scripts` z nonce CSP; bez skryptu menu zostaje rozwinięte. Dodatkowe arkusze przez prop
`stylesheets` (po `View\CoreAssets::STYLESHEETS`). Szerokości: `--ss-sidebar-width`, `--ss-sidebar-collapsed-width`.

Klasy ogólne do czasu komponentów: `.ss-card`, `.ss-card--muted` i `.ss-card--link` (cała karta jako odnośnik, np. skrót
do sekcji) (§4), `.ss-icon--tile` (ikona w kafelku
`surface-muted`), `.ss-onboarding-wizard` (kreator onboardingu: kroki `.ss-stepper` jako odnośniki, karta kroku, stopka Wstecz/Dalej;
krok „Języki” z panelem włączonych języków `.ss-onboarding-languages` nad listą), `.ss-dictionary-code` (kod wpisu
słownika z flagami krajów), `.ss-onboarding` (karta kroków konfiguracji na górze pulpitu: ikona w kafelku, tytuł i opis, po
prawej postęp z paskiem `.ss-progress` — natywny `<progress>` w kolorze akcentu — i lista numerowana kart-odnośników
`.ss-card--muted` z dużym, przygaszonym numerem kroku w tle), `.ss-button:disabled`, oraz warstwy layoutu z §5: `.ss-app`, `.ss-topbar` (z `.ss-search`
i `.ss-avatar`), `.ss-module-nav` (aktywna pozycja przez `aria-current="page"`), `.ss-main`, `.ss-context`,
`.ss-stepper` (`--done`, `--current` z `aria-current="step"`, `--next`; stan także tekstem `.ss-sr-only`; komponent
`x-admiro::stepper` z `steps` i indeksem `current`, teksty `layout.steps.*`), stronicowanie
`x-admiro::pagination` (poprzednia/następna, „Strona X z Y”; przy jednej stronie nic), podpis odręczny
`x-admiro::signature-pad` (płótno, obraz PNG w ukrytym polu, „Wyczyść”; skrypt raz na stronę), plakietka jako przycisk
(`button.ss-badge`, np. odebranie roli),
`.ss-grid` (`--3`, `--wide`), `.ss-stat`, `.ss-list`, `.ss-table`, `.ss-pairs`, `.ss-form-layout`,
`.ss-form-actions`, `.ss-form-row`, `.ss-fieldset`, `.ss-choices` z `.ss-choice` (wybór jednej opcji jako
pigułki), `.ss-grid--4`, `.ss-card__title--inset`, `.ss-table--grid`, `.ss-table__sub`, `.ss-icon-button`,
`.ss-shell`, `.ss-sidebar`, `.ss-tabs` (zakładki sekcji, bieżąca przez `aria-current`), `.ss-card__footer`,
`.ss-account-menu` oraz przełącznik `x-admiro::switch` (`.ss-switch`: checkbox z `role="switch"`, opis
i podpowiedź powiązana przez `aria-describedby`). Panele stref składają z nich swoje widoki, a widoki z `/design-system/views` są wzorem.

## 8. Build assetów: Vite w każdej paczce, uruchamiany ze szkieletu (docelowo)

Jedno `npm run dev` / `npm run build` w szkielecie buduje assety szkieletu i wszystkich paczek.
Każda paczka (także admiro) ma własny Vite, a szkielet tylko je uruchamia.

- **Paczka:** `package.json` (`"private": true`, `"name": "@simplysmart/<paczka>"`, skrypty
  `build` = `vite build`, `dev` = `vite build --watch`), `vite.config.js` i źródła w
  `resources/css/`, `resources/js/`. Tailwind 4 w podejściu CSS-first: `@theme` mapuje tokeny
  `--ss-*` na klasy (`bg-surface`, `text-muted`, `rounded-card`…), `@source` wskazuje widoki
  paczki. Wynik trafia do `public/build/` paczki z własnym `manifest.json` i nie jest commitowany.
- **Szkielet:** jedna, ogólna zmiana — w `package.json` `"workspaces": ["packages/simplysmart/*"]`
  i skrypty `dev`/`build` wołające orkiestrator. `vite.config.js` szkieletu zostaje stockowy.
  Nowa paczka nie wymaga żadnej zmiany w szkielecie.
- **Orkiestrator** (narzędzia deweloperskie `simplysmart/admiro`, skrypt Node `bin/assets.mjs {dev|build}`): znajduje
  paczki z `package.json`. `build` buduje je kolejno, a na końcu szkielet; błąd jednej paczki
  kończy build kodem ≠ 0. `dev` uruchamia równolegle Vite szkieletu i `dev` każdej paczki —
  samo `npm run dev --workspaces` nie wystarczy, bo npm uruchamia workspace'y po kolei, a tryb
  watch się nie kończy.
- **Zależności:** `vite`, `tailwindcss`, `@tailwindcss/vite` w `devDependencies` paczki;
  workspaces instalują je raz, w `node_modules` szkieletu. Nowe zależności wymagają zgody i
  wpisu w [zaleznosci.md](zaleznosci.md).
- **Serwowanie:** komenda Core `php artisan admiro:assets` tworzy symlink
  `public/vendor/<paczka>` → `public/` paczki w `vendor/simplysmart/<paczka>/` (`--copy` kopiuje,
  gdy hosting nie obsługuje symlinków). `public/vendor/` jest generowane i ignorowane przez git.
- **Widoki:** komponent `x-admiro::assets package="…" :entries="[…]"` opakowuje
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
