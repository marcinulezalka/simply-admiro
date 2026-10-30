# Narzędzia deweloperskie

Przybornik dla projektu głównego i wszystkich paczek simplysmart: wydania wersji, paczki prywatne, wdrożenie na serwer
i wspólne skille AI. Przed wersją 0.2.0 admiro był to osobny pakiet `simplysmart/tools`.

Kod leży w `src/Tools/` (`Simplysmart\Admiro\Tools\`), wejścia w `bin/`, skrypt serwerowy w
`resources/deploy/remote.sh`. To czysty PHP CLI uruchamiany na komputerze deweloperskim, przed Laravelem i bez niego:
nie używa frameworka, Core ani klas strefy, a `AdmiroServiceProvider` go nie ładuje.

## Polecenie simplysmart

`vendor/bin/simplysmart` to jedno wejście do narzędzi pakietu, jak `artisan`: nazwy z dwukropkiem
(kropka działa tak samo — `deploy.onboarding` = `deploy:onboarding`), bez nazwy albo z `list` — lista poleceń.

| Polecenie | Co robi |
| --- | --- |
| `release` | wydanie wersji (= `vendor/bin/version-release`) |
| `private:install` | paczki prywatne (= `vendor/bin/composer-private`) |
| `deploy:lock` | manifest wdrożeniowy (= `vendor/bin/composer-private --deploy`) |
| `deploy` | wdrożenie na serwer |
| `deploy:onboarding` | wdrożenie i konta pierwszej instalacji |
| `deploy:fresh` | wdrożenie od zera: `migrate:fresh` (usuwa wszystkie dane), seedery i konta pierwszej instalacji |

Opcje poleceń są takie jak w samodzielnych narzędziach (`--dry-run`, `--root=<katalog>`); `vendor/bin/version-release`
i `vendor/bin/composer-private` działają dalej.

## Wydanie wersji

Projekt i każdy pakiet mają własne `CHANGELOG.md` ([Keep a Changelog](https://keepachangelog.com/pl/1.1.0/))
i `VERSION` (`MAJOR.MINOR.PATCH`). Skrypt czyta wpisy z `## [Unreleased]`, ustala podbicie,
zapisuje nową wersję do `VERSION` i zamienia `[Unreleased]` na datowaną sekcję wydania.

```bash
vendor/bin/version-release                                        # bieżący katalog
vendor/bin/version-release --root=packages/simplysmart/admiro  # inny projekt lub pakiet
vendor/bin/version-release --major-confirm                        # gdy wymagany MAJOR
vendor/bin/version-release --dry-run                              # podgląd: pokazuje wersję, nie zmienia plików
```

| Sekcja w `[Unreleased]` | Podbicie |
| --- | --- |
| `Removed` lub wpis zaczynający się od `**BREAKING**` / `BREAKING:` | MAJOR (wymaga `--major-confirm`) |
| `Added`, `Deprecated` | MINOR |
| `Changed`, `Fixed`, `Security` | PATCH |

Wygrywa najwyższe podbicie wśród niepustych sekcji. Inne nagłówki i wpisy bez nagłówka
`### <sekcja>` są odrzucane. Datę wydania liczy strefa `RELEASE_TIMEZONE`
(domyślnie `Europe/Warsaw`).

## Paczki prywatne

`vendor/bin/composer-private` instaluje paczki z prywatnych repozytoriów tak, żeby publiczny `composer.json` i `composer.lock` projektu ich nie wymieniały. Lista leży w `private-packages.json` w katalogu projektu (dodaj go do `.gitignore`):

```json
{
    "require": {
        "<vendor>/<paczka>": "@dev"
    }
}
```

Narzędzie zapisuje `composer.private.json` (= `composer.json` z dopisanymi paczkami), przy pierwszym uruchomieniu kopiuje `composer.lock` jako `composer.private.lock` (wersje publicznych paczek zostają) i uruchamia `composer update <paczki>` z `COMPOSER=composer.private.json`. Oba pliki dodaj do `.gitignore`.

```bash
vendor/bin/composer-private              # bieżący katalog
vendor/bin/composer-private --dry-run    # pokazuje paczki; nie zapisuje plików i nie uruchamia composera
vendor/bin/composer-private --root=<katalog>
```

Bez `private-packages.json` kończy się kodem 0 i nic nie robi (publiczny klon, CI). Zwykłe `composer install` / `update` usuwa paczki prywatne z `vendor/`, więc potem trzeba uruchomić narzędzie ponownie. W projekcie wygodnie podpiąć je jako skrypt: `"private:install": "@php vendor/bin/composer-private"`.

### Wdrożenie: paczki z repozytoriów git

Lokalnie paczki z repozytoriów typu `path` (np. `packages/*/*`) są dowiązaniami, a `composer.lock` zapisuje je jako „z katalogu”. Na serwerze tych katalogów nie ma, więc tryb `--deploy` buduje osobny manifest wdrożeniowy:

- `composer.deploy.json` — `composer.json` bez repozytoriów `path`, z repozytorium VCS (adres `git remote origin`) każdej paczki z tych katalogów, którą projekt wymaga (`require`, `require-dev`, paczki prywatne), i z dopisanymi paczkami prywatnymi;
- `composer.deploy.lock` — kopia `composer.private.lock` (albo `composer.lock`) zaktualizowana przez `composer update <paczki> --no-install --no-scripts`: paczki przypięte do commitów z repozytoriów zdalnych, `vendor/` bez zmian.

```bash
vendor/bin/composer-private --deploy             # po wypchnięciu repozytoriów paczek
vendor/bin/composer-private --deploy --dry-run   # pokazuje paczki; nie zapisuje plików
```

Paczka z niewypchniętymi commitami dostaje ostrzeżenie (lock wskaże kod z repozytorium zdalnego), paczka bez `origin` to błąd. Oba pliki dodaj do `.gitignore` i wyślij na serwer, a tam: `COMPOSER=composer.deploy.json composer install --no-dev`. Do repozytoriów prywatnych composer potrzebuje tokenu (`composer config -g github-oauth.github.com <token>`). W projekcie: `"deploy:lock": "@php vendor/bin/composer-private --deploy"`.

## Wdrożenie na serwer

`vendor/bin/simplysmart deploy` uruchamia się na komputerze deweloperskim, w katalogu projektu:

1. z `--with-push` najpierw wypycha (`git push`) repozytoria z niewypchniętymi commitami: paczki z `packages/*/*`
   (także prywatne, każda do swojego `origin`), potem projekt; nieudany push zatrzymuje wdrożenie (z `--dry-run`
   tylko pokazuje, co zostałoby wypchnięte),
2. sprawdza, czy projekt i paczki z `packages/*/*` są wypchnięte (niewypchnięte commity zatrzymują wdrożenie,
   niezacommitowane zmiany i nieudany `git fetch` dają ostrzeżenie; katalogi bez `.git` są pomijane),
3. tworzy manifest wdrożeniowy (jak `deploy:lock`; bez repozytoriów `path` zostaje istniejący lock — dlatego projekty bez lokalnych paczek wdraża się z projektu bazowego: `deploy <projekt>`, niżej),
4. wysyła `composer.deploy.json` i `composer.deploy.lock` na serwer (`scp`); środowiska nie wysyła — lokalny
   `.env.production` zostaje na komputerze deweloperskim,
5. uruchamia na serwerze skrypt `resources/deploy/remote.sh` przez `ssh … 'bash -s'`: Composer 2.8+, `.env`
   (`.env.production` leżący na serwerze w katalogu aplikacji jest kopiowany jako `.env` z tymi samymi uprawnieniami
   i zostaje na miejscu; gdy nie podaje `APP_KEY`, zostaje klucz z dotychczasowego `.env`, bo nowy unieważniłby sesje
   i zaszyfrowane dane; bez `.env.production` `.env` zostaje bez zmian; stary `.env.deploy` jest usuwany),
   `git pull --ff-only`, token GitHub, `composer install --no-dev`, `APP_KEY`, migracje (po `config:clear`, więc czytają
   bieżący `.env`), role, słowniki, cache
   i skrypt crona `storage/app/cron.sh` (`admiro:cron:script --php=<DEPLOY_PHP>`, gdy Core ma tę komendę; w panelu
   hostingu: program BASH, ten plik, co minutę).
   Skrypt i token idą standardowym wejściem, więc serwer nie uruchamia narzędzi z admiro, a token nie trafia do listy procesów.

Konfiguracja: `infrastructure/deploy.env` (dodaj do `.gitignore`; w projekcie wzór `deploy.env.example`), format
`KLUCZ=wartość` bez uruchamiania powłoki — wartości ze spacjami w cudzysłowie `"…"` (`\"` i `\\` jako znaki) albo
`'…'` (dosłownie), komentarz po `#`, opcjonalny `export`.

| Klucz | Znaczenie |
| --- | --- |
| `DEPLOY_SSH` | konto SSH serwera (`login@serwer` albo alias z `~/.ssh/config`), wymagane |
| `DEPLOY_PATH` | katalog aplikacji na serwerze (repozytorium projektu z `.env`), wymagane; `~/` = katalog domowy |
| `DEPLOY_PHP` | polecenie PHP 8.4 na serwerze (domyślnie `php84`) |
| `REMOTE_COMPOSER` | plik Composera na serwerze (np. `composer.phar`); pusty = `~/bin/composer` |
| `LOCAL_COMPOSER` | polecenie Composera na komputerze (np. `php composer.phar`); pusty = `composer` |
| `GITHUB_TOKEN` | token tylko do odczytu do repozytoriów paczek; zapisywany w konfiguracji Composera na serwerze |

```bash
vendor/bin/simplysmart deploy --dry-run                    # konfiguracja, repozytoria i plan; nic nie wysyła
vendor/bin/simplysmart deploy
vendor/bin/simplysmart deploy --with-push                  # najpierw git push paczek (także prywatnych) i projektu
vendor/bin/simplysmart deploy --config=infrastructure/test.env
```

### Wdrożenie projektu: przed-produkcja, potem produkcja

W projekcie bazowym z paczkami w `packages/*/*` (np. skeleton) wdrożenie projektu klienta uruchamia się z jego nazwą:
`vendor/bin/simplysmart deploy <projekt>`. Konfiguracja jest w `infrastructure/deploy-vendors/<projekt>.env`
(katalog w `.gitignore`, wzór `infrastructure/deploy-vendor.env.example`, `Tools\Deploy\ProjectConfig`). To klucze
produkcji z tabeli wyżej oraz klucze przed-produkcji, czyli katalogu projektu na tym komputerze (klonu repozytorium
projektu z własnym `.env`):

| Klucz | Znaczenie |
| --- | --- |
| `PREPRODUCTION_PATH` | katalog przed-produkcji (ścieżka bezwzględna, z `artisan` i `.git`), wymagane |
| `PREPRODUCTION_PHP` | polecenie PHP 8.4+ na tym komputerze (domyślnie `php`) |
| `PREPRODUCTION_COMPOSER` | plik Composera (phar) dla skryptu; pusty = `composer` z `PATH` |

Kroki:
1. jak wyżej: `--with-push`, a potem sprawdzenie, że poprawki są wypchnięte w projekcie bazowym i jego paczkach, także prywatnych;
2. świeży manifest wdrożeniowy (stary `composer.deploy.*` jest usuwany, więc nie da się wdrożyć nieaktualnego; bez paczek z repozytoriów `path` to błąd);
3. **przed-produkcja**: manifest kopiowany do jej katalogu i ten sam `resources/deploy/remote.sh` uruchomiony lokalnie (`bash -s` w tym katalogu) z `DEPLOY_KEEP_ENV=1`:
   - `.env` przed-produkcji zostaje bez zmian, bo jej `.env.production` nie jest kopiowany;
   - bez `COMPOSER_BIN` skrypt bierze `composer` z `PATH`;
   - token GitHub nie jest eksportowany, więc nie trafia do globalnej konfiguracji Composera dewelopera;
   - dalej jak na serwerze: `git pull --ff-only`, `composer install --no-dev` z manifestu, migracje, seedy, słowniki, cache;
   - błąd zatrzymuje wdrożenie;
4. pytanie „Wdrożyć też na produkcję …? [t/N]”. `--production` oznacza „tak” bez pytania, a `--no-production` tylko przed-produkcję. Bez terminala (CI, potok) odpowiedź to „nie”;
5. **produkcja**: ten sam manifest przez `scp` i skrypt serwerowy przez `ssh`.

```bash
vendor/bin/simplysmart deploy shop --dry-run          # plan obu środowisk; nic nie uruchamia
vendor/bin/simplysmart deploy shop --with-push        # push, przed-produkcja, pytanie o produkcję
vendor/bin/simplysmart deploy shop --no-production    # tylko przed-produkcja
```

Onboarding i wdrożenie od zera działają tak samo z nazwą projektu:
- `deploy:onboarding <projekt>` bierze konta z `deploy-vendors/<projekt>.onboarding.env` i zakłada je w obu środowiskach;
- `deploy:fresh <projekt> --confirm=<DEPLOY_SSH>` czyści bazę przed-produkcji i produkcji.

Bez nazwy projektu i bez `infrastructure/deploy.env` polecenie wymienia projekty z `deploy-vendors/`.

### Onboarding

`vendor/bin/simplysmart deploy:onboarding` robi to samo wdrożenie, a na końcu zakłada konta pierwszej instalacji
z `infrastructure/deploy.onboarding.env` (dodaj do `.gitignore`; inny plik: `--onboarding=<plik>`):

```bash
ONBOARD_ACCOUNT_1_ZONE=admiro                # strefa: polecenie <strefa>:account:create, wymagane
ONBOARD_ACCOUNT_1_EMAIL=admin@firma.pl       # wymagane
ONBOARD_ACCOUNT_1_NAME="Jan Kowalski"        # wymagane
ONBOARD_ACCOUNT_1_ROLES=hero                 # role po przecinku, opcjonalnie
ONBOARD_ACCOUNT_1_ORGANIZATION=              # token organizacji ról, opcjonalnie
```

Każde konto to `php artisan <strefa>:account:create <e-mail> <imię> --role=… --organization=… --skip-existing`,
więc istniejący adres jest pomijany i onboarding można powtórzyć. Nieznany klucz, brak wymaganej wartości albo
nazwa strefy spoza `[a-z0-9_-]` to błąd przed połączeniem z serwerem. `--dry-run` pokazuje konta bez wysyłania.

### Wdrożenie od zera

`vendor/bin/simplysmart deploy:fresh` to onboarding na pustej bazie: zamiast `migrate` skrypt serwerowy uruchamia
`migrate:fresh`, który **usuwa wszystkie tabele i dane** (konta, organizacje, sesje, dziennik), a potem seedery
i konta z `deploy.onboarding.env` (`--onboarding=<plik>` jak w onboardingu). Nie da się tego cofnąć, więc przed
uruchomieniem zrób kopię bazy, jeśli dane są potrzebne.

```bash
vendor/bin/simplysmart deploy:fresh --dry-run              # plan z ostrzeżeniem; nic nie wysyła
vendor/bin/simplysmart deploy:fresh --confirm=<DEPLOY_SSH> # uruchomienie: wartość musi być równa DEPLOY_SSH
```

Bez `--confirm=` albo z inną wartością niż `DEPLOY_SSH` polecenie kończy się błędem, zanim cokolwiek trafi na serwer.
admiro blokuje destrukcyjne komendy bazy w środowisku `production`. Skrypt serwerowy zdejmuje tę blokadę tylko dla
procesu `migrate:fresh` (`ADMIRO_ALLOW_DESTRUCTIVE_COMMANDS=true` przed komendą, po `config:clear`). Tej zmiennej
nie wpisuje się do `.env`.

## Skille AI

admiro dostarcza wspólne skille dla agentów AI w `resources/boost/skills/`: `solid-dry` (SOLID, DRY, standardy PHP 8.4), `docs-after-tests` (dokumentacja po testach) i `changelog-before-commit` (CHANGELOG i wydania z `--dry-run`). W projekcie Laravel z [Laravel Boost](https://laravel.com/docs/boost) dopisz pakiet do `boost.json`, a Boost skopiuje skille do `.claude/skills/`:

```json
{ "packages": ["simplysmart/admiro"] }
```

```bash
php artisan boost:update
```
