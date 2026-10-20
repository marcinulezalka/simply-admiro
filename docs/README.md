# Dokumentacja simplysmart

| Dokument | Zawartość | Aktualizuj, gdy zmiana dotyczy… |
| --- | --- | --- |
| [Struktura projektu](struktura.md) | katalogi i pliki w katalogu głównym, układ assetów w `public/` | nowego, usuniętego lub przemianowanego katalogu albo pliku w katalogu głównym |
| [Wdrożenie](wdrozenie.md) | hosting współdzielony: DocumentRoot, co wysyłać na serwer | tego, co trafia na serwer (`.gitattributes` `export-ignore`, nowe katalogi deweloperskie) |
| [Baza danych i modele](baza-danych.md) | koncepcja: migracje w Core, makra pól, historia na triggerach, modele, i18n, RODO, tokeny, checksum, mappery | zasad schematu bazy, modeli, migracji, seederów lub mechanizmów Core dla danych |
| [Design system](design-system.md) | koncepcja wyglądu: tokeny, motyw ciemny, karty, warstwy layoutu, komponenty Blade, Tailwind 4 w Core | wyglądu interfejsu, tokenów CSS, wspólnych komponentów lub builda stylów |
| [Strefy](strefy.md) | rejestracja strefy, sesja, język, nagłówki bezpieczeństwa, zaufane proxy, poziomy dostępu tras | mechanizmów stref w Core lub sposobu dodawania strefy |
| [Pakiety zewnętrzne](zaleznosci.md) | zależności Composer, licencje, zasady dodawania | `require` / `require-dev` w `composer.json` (tabela zależności) |

Poza `docs/`:

| Dokument | Aktualizuj, gdy zmiana dotyczy… |
| --- | --- |
| [README.md](../README.md) | opisu projektu, wymagań lub spisu dokumentów. Tylko skrót i linki, szczegóły trafiają do `docs/` |
| [brand/README.md](../brand/README.md) | pliki marki, kolorów lub zasad użycia logo |
| [CONTRIBUTING.md](../CONTRIBUTING.md) | sposobu pracy: polecenia, zasad changelogu, kroków wydania |
| [CHANGELOG.md](../CHANGELOG.md) | zmian widocznych dla użytkownika. Wpis dodaje się przy commicie |

## Zasady dla tej dokumentacji

- Każda informacja ma jedno miejsce. Pozostałe pliki tylko do niego linkują.
- Nazwy plików piszemy małymi literami, bez polskich znaków (`wdrozenie.md`).
- Nowy plik dodajemy dopiero wtedy, gdy ma treść, i dopisujemy go do tych tabel oraz do README.
- Nie dokumentujemy środowiska lokalnego (`infrastructure/`: vhost, PHP-FPM, SSL). Jest zależne od
  maszyny i ignorowane przez git.
