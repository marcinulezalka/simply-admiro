#!/usr/bin/env bash
#
# Część serwerowa wdrożenia (wysyłana na serwer przez `vendor/bin/simplysmart deploy` albo uruchamiana ręcznie w katalogu aplikacji):
#   1. Composer 2.8+ w ~/bin/composer (instaluje, gdy go nie ma; oficjalny instalator ze sprawdzeniem sumy),
#   2. .env (kopia .env.production leżącego na serwerze w katalogu aplikacji — deploy nie wysyła środowiska; APP_KEY
#      serwera zostaje, gdy plik go nie podaje; bez .env.production .env zostaje bez zmian)
#      i pliki wdrożeniowe na miejscu, git pull --ff-only aplikacji,
#   3. token GitHub dla Composera (GITHUB_TOKEN, jeśli podany),
#   4. paczki z manifestu wdrożeniowego (COMPOSER=composer.deploy.json, --no-dev),
#   5. APP_KEY, migracje (z DEPLOY_FRESH=1: migrate:fresh — usuwa wszystkie tabele i dane), słowniki, organizacja operatora, role, słowniki paczek (*:dictionaries:sync), cache.
# Zatrzymuje się przy pierwszym błędzie.
#
# Użycie na serwerze:
#   cd ~/moja-domena.pl && DEPLOY_PHP=php84 bash remote.sh

set -euo pipefail

php_bin="${DEPLOY_PHP:-php84}"
# Plik Composera na serwerze: COMPOSER_BIN (np. composer.phar w katalogu aplikacji), domyślnie ~/bin/composer.
composer_bin="${COMPOSER_BIN:-$HOME/bin/composer}"

fail() { echo "BŁĄD: $*" >&2; exit 1; }
step() { echo "--> $*"; }

command -v "$php_bin" >/dev/null || fail "Brak $php_bin. Ustaw DEPLOY_PHP na polecenie PHP 8.4 na serwerze."
"$php_bin" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' || fail "$php_bin to nie PHP 8.4 ($("$php_bin" -r 'echo PHP_VERSION;'))."
[[ -f artisan && -f composer.json ]] || fail "Uruchom w katalogu aplikacji (brak artisan / composer.json w $(pwd))."

if [[ ! -f "$composer_bin" ]]; then
    step "Instaluję Composera w $composer_bin"
    mkdir -p "$(dirname "$composer_bin")"
    setup="$(mktemp)"
    "$php_bin" -r "copy('https://getcomposer.org/installer', '$setup');" || fail "Nie udało się pobrać instalatora Composera."
    expected="$("$php_bin" -r "echo trim(file_get_contents('https://composer.github.io/installer.sig'));")"
    actual="$("$php_bin" -r "echo hash_file('sha384', '$setup');")"
    [[ -n "$expected" && "$expected" == "$actual" ]] || { rm -f "$setup"; fail "Błędna suma kontrolna instalatora Composera."; }
    "$php_bin" "$setup" --quiet --install-dir="$(dirname "$composer_bin")" --filename="$(basename "$composer_bin")"
    rm -f "$setup"
fi
composer=("$php_bin" "$composer_bin")
if ! "${composer[@]}" --version --no-ansi 2>/dev/null | grep -qE 'version (2\.(8|9|[1-9][0-9])|[3-9])\.'; then
    step "Aktualizuję Composera (wymagany 2.8+ dla PHP 8.4)"
    "${composer[@]}" self-update --2 --no-interaction 2>/dev/null || fail "Nie udało się zaktualizować Composera ($composer_bin)."
fi
step "$("${composer[@]}" --version --no-ansi 2>/dev/null | head -n1)"

rm -f .env.deploy
if [[ -f .env.production ]]; then
    step "Kopiuję .env.production serwera jako .env"
    # Plik bez APP_KEY dostaje klucz z obecnego .env: nowy klucz unieważniłby sesje i zaszyfrowane dane.
    env_new="$(mktemp .env.XXXXXX)"
    if [[ -f .env ]]; then chmod --reference=.env "$env_new"; else chmod 600 "$env_new"; fi
    if ! grep -qE '^APP_KEY=.+' .env.production && [[ -f .env ]] && grep -qE '^APP_KEY=.+' .env; then
        grep -vE '^APP_KEY=' .env.production > "$env_new" || true
        grep -E '^APP_KEY=.+' .env | tail -n1 >> "$env_new"
    else
        cat .env.production > "$env_new"
    fi
    mv -f "$env_new" .env
else
    step "Brak .env.production na serwerze — .env zostaje bez zmian"
fi

[[ -f .env ]] || fail "Brak .env. Skopiuj .env.example jako .env i uzupełnij (baza, APP_URL, poczta)."
[[ -f composer.deploy.json && -f composer.deploy.lock ]] || fail "Brak composer.deploy.json / composer.deploy.lock — uruchom wdrożenie przez vendor/bin/simplysmart deploy."

step "Pobieram kod aplikacji (git pull)"
git pull --ff-only || fail "git pull się nie udał (zmiany lokalne na serwerze albo brak gałęzi śledzonej) — popraw i uruchom ponownie."

# Katalogi, których git nie przenosi (puste), a Laravel ich wymaga (widoki aplikacji, cache, logi).
mkdir -p resources/views storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache


if [[ -n "${GITHUB_TOKEN:-}" ]]; then
    step "Zapisuję token GitHub w konfiguracji Composera"
    "${composer[@]}" config --global github-oauth.github.com "$GITHUB_TOKEN"
fi

step "Instaluję paczki (composer.deploy.json, bez narzędzi deweloperskich)"
if ! grep -qE '^APP_KEY=.+' .env; then
    # Aplikacja nie uruchomi się bez APP_KEY, więc klucz powstaje bez artisana (losowe 32 bajty, base64).
    step "Generuję APP_KEY"
    key="base64:$("$php_bin" -r 'echo base64_encode(random_bytes(32));')"
    if grep -qE '^APP_KEY=' .env; then
        sed -i -E "s|^APP_KEY=.*|APP_KEY=${key}|" .env
    else
        printf '\nAPP_KEY=%s\n' "$key" >> .env
    fi
fi
COMPOSER=composer.deploy.json "${composer[@]}" install --no-dev --optimize-autoloader --no-interaction
[[ -f vendor/autoload.php ]] || fail "Instalacja nie utworzyła vendor/autoload.php."

if [[ "${DEPLOY_FRESH:-}" == 1 ]]; then
    step "Migracje od zera (migrate:fresh — wszystkie tabele i dane zostaną usunięte)"
    # Core blokuje destrukcyjne komendy na produkcji; zmienna zdejmuje blokadę tylko dla tego procesu.
    # Config bez cache, bo zcache'owany config nie czyta zmiennych środowiska.
    "$php_bin" artisan config:clear >/dev/null
    ADMIRO_ALLOW_DESTRUCTIVE_COMMANDS=true "$php_bin" artisan migrate:fresh --force
else
    step "Migracje"
    # Config bez cache, żeby migracje czytały bieżący .env, a nie konfigurację z poprzedniego wdrożenia.
    "$php_bin" artisan config:clear >/dev/null
    "$php_bin" artisan migrate --force
fi

step "Słowniki, organizacja operatora, role i polityki"
# Kolejność z docs/wdrozenie.md admiro: role stref bez firm klientów nadawane są w organizacji operatora.
"$php_bin" artisan admiro:localization:seed
"$php_bin" artisan admiro:organizations:seed
"$php_bin" artisan admiro:permissions:sync
"$php_bin" artisan admiro:legal:seed
for command in $("$php_bin" artisan list --raw | awk '{print $1}' | grep -E ':dictionaries:sync$' || true); do
    "$php_bin" artisan "$command"
done

step "Cache konfiguracji, tras i widoków"
"$php_bin" artisan optimize:clear >/dev/null
"$php_bin" artisan config:cache
"$php_bin" artisan route:cache
"$php_bin" artisan view:cache

# Skrypt crona (harmonogram i worker kolejki) dla panelu hostingu; tylko gdy Core ma tę komendę.
if "$php_bin" artisan list --raw | awk '{print $1}' | grep -qx 'admiro:cron:script'; then
    step "Skrypt crona"
    "$php_bin" artisan admiro:cron:script --php="$php_bin"
fi

step "Wdrożenie zakończone: $("$php_bin" artisan --version)"
