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
use Simplysmart\Admiro\Auth\AccountAlreadyExists;
use Simplysmart\Admiro\Auth\CreateZoneAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\SystemRoleGrant;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Baza komendy paczki strefy zakładającej konto (`<strefa>:account:create {email} {name} [--role=*]
 * [--organization=] [--skip-existing]`).
 *
 * Paczka ustawia tylko `$name`, `$description` i zoneName(); walidacja, strefa żądania
 * i komunikaty są wspólne. PIN właściciel konta ustawia przy pierwszym logowaniu. `--role` nadaje role
 * (konsola, bez sprawdzania eskalacji) w organizacji o tokenie z `--organization`, a w strefie bez `teams`
 * w organizacji operatora; tak powstaje pierwszy administrator (np. `--role=hero`).
 */
abstract class CreateZoneAccountCommand extends Command
{
    /**
     * Opcja: istniejący adres kończy się sukcesem bez zmian (powtarzalny onboarding `simplysmart deploy:onboarding`).
     */
    private const string SKIP_EXISTING = 'skip-existing';

    /**
     * Nazwa strefy z configu paczki (np. `config()->string('shop.zone.name')`).
     */
    abstract protected function zoneName(): string;

    public function handle(
        ValidationFactory $validation,
        ZoneRegistry $zones,
        CurrentZone $current,
        CreateZoneAccount $create,
        SystemRoleGrant $roles,
        ConnectionInterface $db,
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

        $zone = $zones->get($this->zoneName());

        try {
            $db->transaction(fn(): Model => $current->within($zone, function () use ($create, $roles, $zone, $input): Model {
                $account = $create->handle($input['name'], $input['email']);
                $this->grantRoles($roles, $zone, $account);

                return $account;
            }));
        } catch (AccountAlreadyExists) {
            if ($this->option(self::SKIP_EXISTING) === true) {
                $this->info('Konto z tym adresem e-mail już istnieje — pominięte.');

                return self::SUCCESS;
            }

            $this->error('Konto z tym adresem e-mail już istnieje.');

            return self::FAILURE;
        } catch (RoleDoesNotExist|ModelNotFoundException|LogicException $exception) {
            $this->error('Nie nadano ról, konto nie zostało założone: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Konto założone. PIN właściciel konta ustawi przy pierwszym logowaniu.');

        return self::SUCCESS;
    }

    /**
     * @throws RoleDoesNotExist|ModelNotFoundException|LogicException
     */
    private function grantRoles(SystemRoleGrant $roles, ZoneDefinition $zone, Model $account): void
    {
        $names = array_values(array_filter((array) $this->option('role'), is_string(...)));

        if ($names === []) {
            return;
        }

        if (! $account instanceof ZoneAccount) {
            throw new LogicException('Roles need an account model extending ' . ZoneAccount::class . '.');
        }

        $token = $this->option('organization');
        $organization = is_string($token) && $token !== ''
            ? Organization::query()->where(ColumnNames::TOKEN, $token)->firstOrFail()
            : null;

        $roles->grant($account, $roles->organizationFor($zone, $organization), $names);
    }

    /**
     * @return list<InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('role', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Rola konta (można powtórzyć)'),
            new InputOption('organization', null, InputOption::VALUE_REQUIRED, 'Token organizacji ról (strefa z organizacjami klientów)'),
            new InputOption(self::SKIP_EXISTING, null, InputOption::VALUE_NONE, 'Istniejące konto nie jest błędem (onboarding wdrożenia)'),
        ];
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
