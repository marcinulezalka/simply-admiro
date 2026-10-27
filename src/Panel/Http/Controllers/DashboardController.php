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

namespace Simplysmart\Admiro\Panel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Onboarding\OnboardingProgress;

/**
 * Dashboard (pulpit administratora): jedna karta prowadząca do Onboardingu z postępem zadań, do których administrator
 * ma uprawnienia; przegląd zadań jest w OnboardingController::index.
 */
final readonly class DashboardController
{
    public function __invoke(AdminContext $context, OnboardingProgress $progress): View
    {
        return view('admiro::panel.dashboard', ['onboarding' => $progress->checklist(OnboardingStep::permittedFor($context))]);
    }
}
