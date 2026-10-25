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

namespace Simplysmart\Mainboard\Providers;

use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Contracts\DesignSystemCatalog;
use Simplysmart\Mainboard\Enums\DesignSystemArea;
use Simplysmart\Mainboard\Enums\DesignSystemView;
use Simplysmart\Mainboard\View\DesignSystem\InMemoryDesignSystemCatalog;
use Simplysmart\Mainboard\View\DesignSystem\SignInPreviews;
use Simplysmart\Mainboard\View\Demo\DemoInvoices;

/**
 * Katalog design systemu: wiązanie kontraktu (singleton) i widoki Core z enumów DesignSystemArea/DesignSystemView.
 * Paczki stref dokładają swoje obszary i widoki przez kontrakt w swoich providerach.
 */
final class DesignSystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DesignSystemCatalog::class, InMemoryDesignSystemCatalog::class);
        $this->callAfterResolving(DesignSystemCatalog::class, $this->registerCoreViews(...));
    }

    private function registerCoreViews(DesignSystemCatalog $catalog): void
    {
        foreach (DesignSystemArea::cases() as $area) {
            $catalog->addArea($area->value, $area->labelKey());
        }

        foreach (DesignSystemView::cases() as $view) {
            $catalog->addView($view->area()->value, $view->value, $view->labelKey(), $view->template(), fn(): array => [
                'view' => $view,
                'invoices' => DemoInvoices::all(),
                ...$this->app->make(SignInPreviews::class)->data($view),
            ]);
        }
    }
}
