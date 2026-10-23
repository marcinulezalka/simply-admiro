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

namespace Simplysmart\Mainboard\Http;

use Illuminate\Routing\Router;
use Simplysmart\Mainboard\Auth\SignIn\SignInFlow;
use Simplysmart\Mainboard\Auth\SignIn\Steps\EmailCodeStep;
use Simplysmart\Mainboard\Http\Controllers\Auth\LoginController;
use Simplysmart\Mainboard\Http\Controllers\Auth\LogoutController;
use Simplysmart\Mainboard\Http\Controllers\Auth\ResendCodeController;
use Simplysmart\Mainboard\Http\Controllers\Auth\SignInStepController;
use Simplysmart\Mainboard\Http\Middleware\EnsureSignInStep;
use Simplysmart\Mainboard\Zones\AccessTier;
use Simplysmart\Mainboard\Zones\ZoneDefinition;
use Simplysmart\Mainboard\Auth\AuthBrand;
use Simplysmart\Mainboard\Http\Controllers\Auth\AuthBrandController;

/**
 * Trasy logowania strefy (wewnątrz grupy strefy): `auth.login`, `auth.<krok>` dla każdego kroku
 * z configu, `auth.code.resend` i `auth.logout`. Każdy POST ma limit `mainboard.sign-in`.
 */
final readonly class SignInRoutes
{
    public const string THROTTLE = 'mainboard.sign-in';

    private const string PREFIX = 'signin';

    /** Strona „Nie pamiętam PIN-u” (nazwa względem prefiksu `auth.`): jak ustawić nowy PIN po resecie. */
    public const string FORGOT_PIN = 'forgot-pin';

    private const string FORGOT_PIN_PATH = 'forgot-pin';

    /** Plik banera logowania strefy (nazwa względem prefiksu `auth.`), gdy strefa ma `auth.brand`. */
    public const string BRAND = 'brand';

    public function __construct(
        private Router $router,
        private SignInFlow $flow,
    ) {}

    /**
     * Nazwa trasy kroku względem prefiksu strefy.
     */
    public static function stepRoute(string $step): string
    {
        return 'auth.' . $step;
    }

    public function register(ZoneDefinition $zone): void
    {
        $throttle = 'throttle:' . self::THROTTLE;

        $this->router->middleware(AccessTier::GuestOnly->middleware($zone))->prefix(self::PREFIX)->name('auth.')->group(function () use ($zone, $throttle): void {
            $this->router->get('/', [LoginController::class, 'create'])->name('login');
            $this->router->post('/', [LoginController::class, 'store'])->middleware($throttle)->name('login.store');
            $this->router->view(self::FORGOT_PIN_PATH, 'mainboard::auth.forgot-pin')->name(self::FORGOT_PIN);

            if ($zone->auth()->brand !== null) {
                $this->router->get('brand/{variant}', AuthBrandController::class)->where('variant', AuthBrand::LIGHT . '|' . AuthBrand::DARK)->name(self::BRAND);
            }

            foreach ($this->flow->steps($zone->auth()) as $step) {
                $key = $step->key();
                $guard = EnsureSignInStep::class . ':' . $key;

                $this->router->get($key, [SignInStepController::class, 'show'])->defaults('step', $key)->middleware($guard)->name($key);
                $this->router->post($key, [SignInStepController::class, 'store'])->defaults('step', $key)->middleware([$guard, $throttle])->name($key . '.store');

                if ($key === EmailCodeStep::KEY) {
                    $this->router->post($key . '/resend', ResendCodeController::class)->middleware([$guard, $throttle])->name($key . '.resend');
                }
            }
        });

        $this->router->middleware(AccessTier::Authenticated->middleware($zone))->post(self::PREFIX . '/out', LogoutController::class)->name('auth.logout');
    }
}
