# Wdrożenie — hosting współdzielony

## DocumentRoot

Jeśli panel na to pozwala, ustaw katalog domeny na `public/`. Wtedy reszta projektu nie jest
dostępna z sieci.

Jeśli panel wymusza stały katalog (np. `public_html/`), wyślij do niego **tylko zawartość
`public/`**, a resztę kodu aplikacji umieść poziomem wyżej, poza katalogiem publicznym.

## Czego nie wysyłać na serwer

| Ścieżka | Powód |
| --- | --- |
| `brand/` | źródła marki; serwis używa kopii z `public/` |
| `docs/` | dokumentacja |
| `.claude/`, `.git/` | skille AI i historia repozytorium |
| `.env*` | dane środowiska. Na serwerze tworzy się osobny plik |
| `node_modules/`, `resources/css/`, `resources/js/` | źródła i narzędzia builda; serwer dostaje tylko wynik (zob. niżej) |

## Baza danych

Tabele danych mają historię zmian zapisywaną triggerami MySQL (`docs/baza-danych.md` §4), więc
użytkownik bazy potrzebuje uprawnienia `TRIGGER`. Przy włączonym binlogu (replikacja) serwer wymaga
też `log_bin_trust_function_creators = 1` albo uprawnienia `SUPER` — sprawdź to u dostawcy przed
pierwszą migracją.

Po wysłaniu kodu:

```bash
php artisan migrate --force              # migracje Core (destrukcyjne komendy są na produkcji zablokowane)
php artisan mainboard:localization:seed  # słowniki lokalizacji; bezpieczne przy każdym wdrożeniu
php artisan mainboard:organizations:seed # organizacja operatora (zakres ról stref bez firm); bezpieczne przy każdym wdrożeniu
php artisan icons:cache                  # manifest ikon blade-icons (flagi), bez skanowania SVG przy każdym żądaniu
```

## Assety

> **Koncepcja** — obowiązuje, gdy paczki dostaną własny Vite
> (zob. [design-system.md](design-system.md#8-build-assetów-vite-w-każdej-paczce-uruchamiany-ze-szkieletu-docelowo)).

Hosting współdzielony zwykle nie ma Node.js, więc assety buduje się przed wysyłką (lokalnie
albo w CI), w katalogu szkieletu:

```bash
npm ci
npm run build                          # szkielet i wszystkie paczki
php artisan mainboard:assets --copy    # kopiuje public/build paczek do public/vendor/<paczka>/
```

Na serwer trafia `public/` szkieletu razem z `public/build/` i `public/vendor/*/build/`.
`--copy` zamiast symlinku, bo FTP i wiele hostingów nie przenosi symlinków.

## Paczka przez `git archive`

`brand/`, `docs/` i `.claude/` mają w `.gitattributes` regułę `export-ignore`, więc paczka ich nie zawiera:

```bash
git archive --format=zip -o mainboard.zip HEAD
```

## Wdrożenie przez FTP

Klient FTP nie czyta `.gitattributes`. Dodaj ścieżki z tabeli powyżej do listy wykluczeń
w kliencie (np. filtry w FileZilla) albo w skrypcie wdrożeniowym.
