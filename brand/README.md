# Marka admiro

Pliki źródłowe marki strefy administratorów. Nie są wdrażane (`export-ignore`); strefa używa ich
kopii w `public/images/`, podłączonych w `config/admiro.php` (`zone.brand`, `zone.favicon`).

## Zawartość

| Plik | Do czego |
| --- | --- |
| `logo/admiro.svg` | wzorzec logotypu „admiro” (wektor, czarne litery zamienione na krzywe, złota korona zamiast kropki nad „i”). Z niego powstają pozostałe wersje |
| `logo/png/admiro-on-light-{800,1600,2400}.png` | logo z czarnymi literami, na jasne tło: dokumenty, prezentacje |
| `logo/png/admiro-on-dark-{800,1600,2400}.png` | logo z białymi literami, na ciemne tło |
| `icon/admiro-mark-on-light.png`, `icon/admiro-mark-on-dark.png` | sygnet: złota korona z dolną kreską (na jasne i ciemne tło ten sam); 1024×1024, przezroczyste tło |
| `OFL.txt` | licencja kroju Zen Dots |

Nazwy `on-light` / `on-dark` mówią o **tle**, na którym logo stoi, nie o kolorze liter. Korona jest zawsze złota.

## Pliki strefy (`public/images/`)

| Plik | Typ w configu | Do czego |
| --- | --- | --- |
| `logo.svg` | — | logotyp do osadzenia inline (litery `fill="currentColor"`, przełączają się z motywem; korona złota) |
| `logo.png` | `brand.logo` | logo w szablonie e-maila (800 px szerokości, czarne litery i złota korona, przezroczyste tło, wolne pole) |
| `signet.png` | `brand.signet` | awatar: złota korona z dolną kreską na czarnym kwadracie 512×512 (bez zaokrągleń, więc znosi przycięcie do koła) |
| `favicon.svg` | `favicon.svg` | ikona karty przeglądarki: złota korona z dolną kreską na czarnym zaokrąglonym kwadracie |
| `favicon.ico` | `favicon.ico` | to samo dla starszych przeglądarek (16, 32, 48 px) |
| `apple-touch-icon.png` | `favicon.apple` | ikona ekranu startowego (180×180, pełny kwadrat — system sam zaokrągla) |

## Krój

Litery logotypu to krój **Zen Dots** (The Dots Project Authors, SIL Open Font License 1.1 — `OFL.txt`),
zamienione na krzywe, więc nie wymagają fontu. Korona (w logo zamiast kropki nad „i”, w sygnecie i ikonach
z dolną kreską) to wypełniony zewnętrzny kontur ikony korony na siatce 20×20 (ostre szpice, jak krój liter). Licencja OFL pozwala używać kroju w logo;
nie rozpowszechniaj samego pliku fontu pod inną nazwą.

## Kolory i zasady

Litery logo występują tylko w czerni (`#000000`) albo bieli (`#ffffff`), jak marka simplysmart; korona jest
zawsze złota (`#F5B301`), także w sygnecie i ikonach strony (na czarnym tle). Nie rozciągaj,
nie obracaj i nie zmieniaj proporcji logo; wokół zostaw wolne pole co najmniej ¼ wysokości liter.
