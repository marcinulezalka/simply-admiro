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

return [

    'code' => 'Błąd :code',
    'home' => 'Strona główna',
    'back' => 'Wróć',
    'retry' => 'Spróbuj ponownie',

    '401' => [
        'title' => 'Zaloguj się, aby kontynuować',
        'message' => 'Ta strona wymaga zalogowania w odpowiedniej części aplikacji.',
    ],
    '403' => [
        'title' => 'Brak dostępu',
        'message' => 'Twoje konto nie ma uprawnień do tej strony. Jeśli to pomyłka, poproś administratora o dostęp.',
    ],
    '404' => [
        'title' => 'Nie znaleziono strony',
        'message' => 'Adres mógł się zmienić albo strona została usunięta. Sprawdź adres albo wróć na stronę główną.',
    ],
    '419' => [
        'title' => 'Strona wygasła',
        'message' => 'Formularz był otwarty zbyt długo. Wróć, odśwież stronę i wyślij go ponownie.',
    ],
    '429' => [
        'title' => 'Zbyt wiele żądań',
        'message' => 'Wysłano zbyt wiele żądań w krótkim czasie. Odczekaj chwilę i spróbuj ponownie.',
    ],
    '500' => [
        'title' => 'Coś poszło nie tak',
        'message' => 'Wystąpił nieoczekiwany błąd. Zapisaliśmy go i już nad nim pracujemy. Spróbuj ponownie za chwilę.',
    ],
    '503' => [
        'title' => 'Trwają prace techniczne',
        'message' => 'Aplikacja jest chwilowo niedostępna. Wróć za kilka minut.',
    ],

];
