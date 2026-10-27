<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

/*
 * Słownik pojęć (Wiki\Wiki, x-admiro::wiki-tip): <klucz>.term — słowo kluczowe, .definition — definicja w ujęciu
 * systemu, .example — przykład (opcjonalny). Paczka albo aplikacja może podać własne ujęcie pojęcia nadpisaniem.
 */
return [
    'label' => 'Wiki',
    'example' => 'Przykład',
    'title' => 'Wiki — słownik pojęć',
    'lead' => 'Pojęcia systemu w jednym miejscu: definicje, przykłady i odpowiedzi na częste pytania. Każdy wpis ma znacznik wiki-…, do którego można linkować.',
    'search_label' => 'Szukaj w słowniku',
    'search_placeholder' => 'Pojęcie, definicja albo pytanie',
    'none' => 'Brak pojęć pasujących do „:term”.',
    'organizacja' => [
        'term' => 'Organizacja',
        'definition' => 'Firma albo jej jednostka, w której pracują konta stref. Organizacja ma swoje dane, członków i role, a organizacje układają się w drzewo: firma → oddziały → departamenty. Wyłączona organizacja zostaje w systemie z danymi, tylko jej członkowie nie mogą w niej pracować.',
        'example' => 'Grupa Medyczna Nowak (firma) → Oddział Północ (jednostka z własnymi danymi) → Poradnia kardiologiczna (departament oddziału).',
    ],
    'organizacja-operatora' => [
        'term' => 'Organizacja operatora',
        'question' => 'Czy organizację operatora można wyłączyć albo przenieść?',
        'definition' => 'Organizacja, która obsługuje aplikację. Zawsze aktywna i na najwyższym poziomie drzewa; w niej pracują administratorzy i strefy bez organizacji klientów.',
        'example' => 'Firma wdrażająca system dla swoich klientów — jej konta administratorów nie należą do żadnej organizacji klienta.',
    ],
    'departament' => [
        'term' => 'Departament',
        'question' => 'Czym departament różni się od oddziału?',
        'definition' => 'Dział jednostki: osobny kontekst pracy z własnymi członkami i rolami, ale bez własnych danych — dane należą do jednostki. Leży zawsze bezpośrednio pod jednostką i nie ma pod sobą organizacji.',
        'example' => 'Poradnia kardiologiczna w Oddziale Północ: lekarz poradni pracuje w jej kontekście, a pacjenci są pacjentami oddziału.',
    ],
    'strefa' => [
        'term' => 'Strefa',
        'question' => 'Czy jedna osoba może pracować w kilku strefach?',
        'definition' => 'Oddzielona część aplikacji z własnymi kontami, logowaniem, sesją i uprawnieniami. Jedna organizacja może mieć członków z kilku stref, ale strefy nie dzielą kont.',
        'example' => 'Strefa administratorów i strefa personelu: ta sama osoba ma dwa niezależne konta i loguje się do każdej osobno.',
    ],
    'wlasciciel' => [
        'term' => 'Właściciel',
        'definition' => 'Konto strefy z rolą właściciela w organizacji: zarządza nią w swojej strefie i dodaje kolejnych członków z rolami. Zakłada go administrator.',
        'example' => 'Administrator zakłada właściciela dla Oddziału Północ, a właściciel dodaje lekarzy i rejestrację.',
    ],
];
