# Marka simplysmart

Pliki źródłowe marki. Nie są serwowane ani wdrażane (`export-ignore`); do serwisu trafiają ich
kopie w `public/` — zob. [docs/struktura.md](../../docs/struktura.md#assety).

## Zawartość

| Plik | Do czego |
| --- | --- |
| `logo/simplysmart.svg` | wzorzec logotypu (wektor, czarny). Z niego powstają wszystkie pozostałe wersje |
| `logo/png/simplysmart-on-light-{800,1600,2400}.png` | logo czarne, na jasne tło: maile, dokumenty, prezentacje |
| `logo/png/simplysmart-on-dark-{800,1600,2400}.png` | logo białe, na ciemne tło |
| `icon/simplysmart-mark-mole-on-dark.png` | sygnet „S”, biały, na ciemne tło; 1024×1024, przezroczyste tło |
| `icon/simplysmart-mark-mole-on-light.png` | sygnet „S”, czarny, na jasne tło; 1024×1024, przezroczyste tło |

Nazwy `on-light` / `on-dark` mówią o **tle**, na którym logo stoi, nie o kolorze logo.

## Sygnet

Litera „S” w stylu kroju Mole (Myles Hunt Design): dwie grube kreski z zaokrąglonymi końcami,
z gradientem wzdłuż kreski — górna ciemnieje na łuku i chowa się pod środkową belką, dolna belka
zanika w lewo. Narysowana od nowa na podstawie plakatu kroju, nie wyjęta z pliku fontu. Przed
użyciem jako znak towarowy sprawdź licencję kroju albo odróżnij literę od oryginalnego glifu.

## Kolory

| Nazwa | HEX | Token CSS |
| --- | --- | --- |
| Czerń | `#000000` | `--ss-black` |
| Biel | `#ffffff` | `--ss-white` |

Logo występuje tylko w czerni albo bieli — bez gradientów, cieni i innych kolorów. Pełna lista
tokenów (kolory semantyczne, typografia, odstępy): `public/assets/css/tokens.css`.

## Zasady użycia logo

- Na stronie używaj `public/assets/img/brand/logo.svg` osadzonego inline: ma `fill="currentColor"`,
  więc przyjmuje kolor tekstu i sam przełącza się w motywie ciemnym.
- PNG tylko tam, gdzie SVG nie działa (maile, dokumenty). Wybierz najmniejszy rozmiar, który
  w docelowym miejscu ma co najmniej 2× piksele szerokości wyświetlania.
- Wokół logo zostaw wolne pole co najmniej wysokości kropki nad „i”.
- Nie rozciągaj, nie obracaj i nie zmieniaj proporcji logo.
