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

namespace Simplysmart\Admiro\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Simplysmart\Mainboard\Auth\AccountAlreadyExists;
use Simplysmart\Mainboard\Auth\CreateZoneAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Zakłada konto administratora; strefa nie ma rejestracji, a PIN administrator ustawia sam
 * przy pierwszym logowaniu.
 */
final class CreateAdmiroAccount extends Command
{
    protected $signature = 'admiro:account:create
        {email : Adres e-mail administratora}
        {name : Imię i nazwisko}';

    protected $description = 'Zakłada konto administratora strefy admiro';

    public function handle(
        ValidationFactory $validation,
        ZoneRegistry $zones,
        CurrentZone $current,
        CreateZoneAccount $create,
    ): int {
        $input = ['email' => $this->trimmedArgument('email'), 'name' => $this->trimmedArgument('name')];
        $validator = $validation->make($input, [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $current->set($zones->get(config()->string('admiro.zone.name')));

        try {
            $create->handle($input['name'], $input['email']);
        } catch (AccountAlreadyExists) {
            $this->error('Konto z tym adresem e-mail już istnieje.');

            return self::FAILURE;
        } finally {
            $current->forget();
        }

        $this->info('Konto administratora założone. PIN administrator ustawi przy pierwszym logowaniu.');

        return self::SUCCESS;
    }

    private function trimmedArgument(string $name): string
    {
        $value = $this->argument($name);

        return is_string($value) ? trim($value) : '';
    }
}
