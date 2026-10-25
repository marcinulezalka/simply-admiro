# Marka admiro

Pliki źródłowe marki strefy administratorów. Nie są wdrażane (`export-ignore`); strefa używa ich
kopii w `public/images/`, podłączonych w `config/admiro.php` (`zone.brand`, `zone.favicon`).

## Zawartość

| Plik | Do czego |
| --- | --- |
| `logo/admiro.svg` | wzorzec logotypu „admiro” (wektor, czarny, litery zamienione na krzywe). Z niego powstają pozostałe wersje |
| `logo/png/admiro-on-light-{800,1600,2400}.png` | logo czarne, na jasne tło: dokumenty, prezentacje |
| `logo/png/admiro-on-dark-{800,1600,2400}.png` | logo białe, na ciemne tło |
| `icon/admiro-mark-on-light.png` | sygnet „a”, czarny, na jasne tło; 1024×1024, przezroczyste tło |
| `icon/admiro-mark-on-dark.png` | sygnet „a”, biały, na ciemne tło; 1024×1024, przezroczyste tło |
| `OFL.txt` | licencja kroju Zen Dots |

Nazwy `on-light` / `on-dark` mówią o **tle**, na którym logo stoi, nie o kolorze logo.

## Pliki strefy (`public/images/`)

| Plik | Typ w configu | Do czego |
| --- | --- | --- |
| `logo.svg` | — | logotyp do osadzenia inline (`fill="currentColor"`, przełącza się z motywem) |
| `logo.png` | `brand.logo` | logo w szablonie e-maila (800 px szerokości, czarne, przezroczyste tło, wolne pole) |
| `signet.png` | `brand.signet` | awatar: białe „a” na czarnym kwadracie 512×512 (bez zaokrągleń, więc znosi przycięcie do koła) |
| `favicon.svg` | `favicon.svg` | ikona karty przeglądarki: białe „a” na czarnym zaokrąglonym kwadracie |
| `favicon.ico` | `favicon.ico` | to samo dla starszych przeglądarek (16, 32, 48 px) |
| `apple-touch-icon.png` | `favicon.apple` | ikona ekranu startowego (180×180, pełny kwadrat — system sam zaokrągla) |

## Krój

Logotyp i sygnet to krój **Zen Dots** (The Dots Project Authors, SIL Open Font License 1.1 — `OFL.txt`),
z literami zamienionymi na krzywe, więc nie wymagają fontu. Licencja OFL pozwala używać kroju w logo;
nie rozpowszechniaj samego pliku fontu pod inną nazwą.

## Kolory i zasady

Logo występuje tylko w czerni (`#000000`) albo bieli (`#ffffff`), jak marka simplysmart. Nie rozciągaj,
nie obracaj i nie zmieniaj proporcji logo; wokół zostaw wolne pole co najmniej ¼ wysokości liter.
