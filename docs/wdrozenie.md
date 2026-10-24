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
| `packages/` | paczki pobiera na serwerze Composer z ich repozytoriów git (zob. niżej) |
| `node_modules/`, `resources/css/`, `resources/js/` | źródła i narzędzia builda; serwer dostaje tylko wynik (zob. niżej) |

## Paczki na serwerze (Composer z GitHuba)

Lokalnie paczki są dowiązaniami z `packages/` (repozytorium typu path). Serwer ich nie dostaje: Composer
pobiera paczki z ich repozytoriów git do `vendor/`, według manifestu wdrożeniowego.

Jednorazowo na serwerze:

```bash
# Composer 2.8+ na PHP 8.4 w katalogu domowym
php84 -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php84 composer-setup.php --install-dir="$HOME/bin" --filename=composer && rm composer-setup.php
# token GitHub tylko do odczytu (fine-grained, „Contents: read” dla repozytoriów paczek, także prywatnych)
php84 ~/bin/composer config -g github-oauth.github.com <token>
```

Przy każdym wdrożeniu:

```bash
# lokalnie, po wypchnięciu wszystkich repozytoriów (paczek i aplikacji)
composer deploy:lock            # composer.deploy.json + composer.deploy.lock (w .gitignore)
# wyślij kod aplikacji i oba pliki; na serwerze (z .env i APP_KEY):
COMPOSER=composer.deploy.json php84 ~/bin/composer install --no-dev --optimize-autoloader
php84 artisan migrate --force
php84 artisan config:cache && php84 artisan route:cache && php84 artisan view:cache
```

Lock przypina paczki do commitów z GitHuba, więc niewypchnięty kod nie trafi na serwer (`deploy:lock` ostrzega
o niewypchniętych commitach). Domyślne `php` na hostingu może być starszą wersją — wszystkie polecenia
uruchamiaj przez PHP 8.4 (także w cronie).

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
php artisan mainboard:permissions:sync   # role i uprawnienia stref z plików JSON; idempotentna, bez --prune nic nie usuwa
php artisan icons:cache                  # manifest ikon blade-icons (flagi), bez skanowania SVG przy każdym żądaniu
```

Harmonogram Laravela musi działać z crona (`* * * * * php /ścieżka/artisan schedule:run`): Core planuje
`mainboard:auth:prune` (co godzinę: kody logowania, zaufane urządzenia), `mainboard:audit:prune` (codziennie:
dziennik zdarzeń) i `mainboard:exchange-rates:sync` (w dni robocze o `EXCHANGE_RATES_SCHEDULE_AT`, domyślnie 12:30:
kursy tabeli A NBP z ostatnich `EXCHANGE_RATES_DAYS_BACK` dni; pusta godzina wyłącza; `NBP_API_URL`,
`NBP_API_TIMEOUT`), `mainboard:holidays:sync` (pierwszego dnia miesiąca: święta aktywnych krajów na bieżący i następny
rok z `date.nager.at`; `HOLIDAYS_SCHEDULE=false` wyłącza, `HOLIDAYS_API_URL`, `HOLIDAYS_API_TIMEOUT`) i `mainboard:cookies:prune` (codziennie: rejestr zgód na cookies starszy niż
`COOKIE_CONSENT_RETENTION_DAYS`). Serwer potrzebuje wyjścia HTTPS do `api.nbp.pl` i `date.nager.at`.

Zgoda na cookies (`COOKIE_CONSENT`, domyślnie włączona): baner pojawia się po opublikowaniu polityki cookies
w panelu `admiro` („Polityka cookies”); ciasteczko zgody `COOKIE_CONSENT_NAME` ważne `COOKIE_CONSENT_DAYS` dni.
Zewnętrzny skrypt opcjonalny (np. analityka) wymaga dopisania jego domeny do `mainboard.security.csp.policy` —
robi to paczka, która go rejestruje (`config([...])` w swoim providerze).

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
