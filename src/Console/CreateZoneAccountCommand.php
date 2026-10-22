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

namespace Simplysmart\Mainboard\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Simplysmart\Mainboard\Auth\AccountAlreadyExists;
use Simplysmart\Mainboard\Auth\CreateZoneAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Baza komendy paczki strefy zakładającej konto (`<strefa>:account:create {email} {name}`).
 *
 * Paczka ustawia tylko `$name`, `$description` i zoneName(); walidacja, strefa żądania
 * i komunikaty są wspólne. PIN właściciel konta ustawia przy pierwszym logowaniu.
 */
abstract class CreateZoneAccountCommand extends Command
{
    /**
     * Nazwa strefy z configu paczki (np. `config()->string('shop.zone.name')`).
     */
    abstract protected function zoneName(): string;

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

        try {
            $current->within($zones->get($this->zoneName()), static fn(): mixed => $create->handle($input['name'], $input['email']));
        } catch (AccountAlreadyExists) {
            $this->error('Konto z tym adresem e-mail już istnieje.');

            return self::FAILURE;
        }

        $this->info('Konto założone. PIN właściciel konta ustawi przy pierwszym logowaniu.');

        return self::SUCCESS;
    }

    /**
     * @return list<InputArgument>
     */
    protected function getArguments(): array
    {
        return [
            new InputArgument('email', InputArgument::REQUIRED, 'Adres e-mail'),
            new InputArgument('name', InputArgument::REQUIRED, 'Imię i nazwisko'),
        ];
    }

    private function trimmedArgument(string $name): string
    {
        $value = $this->argument($name);

        return is_string($value) ? trim($value) : '';
    }
}
