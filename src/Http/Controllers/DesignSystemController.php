<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\View\View;
use Simplysmart\Mainboard\Contracts\DesignSystemCatalog;
use Simplysmart\Mainboard\View\CoreAssets;
use Simplysmart\Mainboard\View\DesignTokens;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Strona design systemu (tokeny i komponenty Core na żywo) i gotowe widoki z katalogu (Contracts\DesignSystemCatalog). Włączenie sprawdzane przy żądaniu, nie przy rejestracji
 * trasy, więc `route:cache` zbudowane w innym środowisku niczego nie odsłania; wyłączona strona to 404.
 */
final readonly class DesignSystemController
{
    public const string TOKENS_FILE = 'css/tokens.css';

    public const string ICONS_DIRECTORY = 'img/icons';

    public function __construct(
        private Repository $config,
        private DesignSystemCatalog $catalog,
    ) {}

    /**
     * @throws NotFoundHttpException Gdy strona jest wyłączona (`mainboard.design_system.enabled`).
     */
    public function index(): View
    {
        $this->ensureEnabled();

        return view('mainboard::design-system.index', [
            'tokens' => DesignTokens::fromCss(CoreAssets::contents(self::TOKENS_FILE)),
            'icons' => CoreAssets::names(self::ICONS_DIRECTORY, 'svg'),
        ]);
    }

    /**
     * Jeden gotowy widok z katalogu (Core albo paczki strefy) z jego danymi i `$preview` (bieżący widok dla nawigacji).
     *
     * @throws NotFoundHttpException Gdy strona jest wyłączona albo widoku nie ma w katalogu.
     */
    public function show(string $view): View
    {
        $this->ensureEnabled();
        $preview = $this->catalog->find($view) ?? throw new NotFoundHttpException();

        return view($preview->template, [...$preview->data(), 'preview' => $preview]);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function ensureEnabled(): void
    {
        if (! $this->config->boolean('mainboard.design_system.enabled')) {
            throw new NotFoundHttpException();
        }
    }
}
