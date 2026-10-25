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

namespace Simplysmart\Admiro\Providers;

use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Contracts\DesignSystemCatalog;
use Simplysmart\Admiro\Enums\DesignSystemArea;
use Simplysmart\Admiro\Enums\DesignSystemView;
use Simplysmart\Admiro\View\DesignSystem\InMemoryDesignSystemCatalog;
use Simplysmart\Admiro\View\DesignSystem\SignInPreviews;
use Simplysmart\Admiro\View\Demo\DemoInvoices;

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
