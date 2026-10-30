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

namespace Simplysmart\Admiro\Enums;

/**
 * Wariant layoutu aplikacji po zalogowaniu (`app_layout` w configu strefy, x-admiro::app.layout): Sidebar — menu
 * boczne z osobnym menu ustawień, wylogowaniem i logo simplysmart na dole; Workspace — ustawienia i konto w głównym
 * menu, akcje pod nazwą konta jako ikony z podpowiedziami (wylogowanie po prawej; w zwiniętym menu w rozwijanym menu
 * pod błyskawicą), powiadomienia w panelu wysuwanym przy krawędzi menu, nagłówki grup nad kartami i stopka treści
 * z logo simplysmart, przełącznikiem motywu i mapą strony (layout strefy administratorów); Zone — bazowy layout stref
 * (x-admiro::app.zone-layout): nagłówek z odnośnikiem ← i dużym tytułem, treść na tle płótna, stopka z kontem (tylko
 * profil i wylogowanie), wyszukiwarką i narzędziami (przełącznik kontekstu, data otwierająca dwa panele boczne), bez mapy
 * strony.
 */
enum AppLayout: string
{
    case Sidebar = 'sidebar';
    case Workspace = 'workspace';
    case Zone = 'zone';
}
