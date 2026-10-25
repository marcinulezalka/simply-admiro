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

namespace Simplysmart\Mainboard\Models\Concerns;

use Simplysmart\Mainboard\Auth\AccountColumns;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\AvatarColor;

/**
 * Kolor awatara konta w `json_attributes` ({@see AvatarColor::ATTRIBUTE}): przydzielany przy zakładaniu konta
 * (z adresu e-mail), odczytywany przez avatarColor(); konto bez zapisanego koloru dostaje ten sam kolor wyliczony.
 * Kolor jest ustawiany przy `saving` nowego modelu, więc w modelu ten koncern stoi przed HasChecksum (zdarzenia
 * uruchamiają się w kolejności deklaracji) i suma kontrolna obejmuje już kolor.
 *
 * @method static void saving(\Closure|string $callback) Metoda modelu Eloquent.
 * @method mixed getAttribute(string $key) Metoda modelu Eloquent.
 * @method $this setAttribute(string $key, mixed $value) Metoda modelu Eloquent.
 */
trait HasAvatarColor
{
    public static function bootHasAvatarColor(): void
    {
        static::saving(static function (self $model): void {
            if ($model->exists) {
                return;
            }

            $attributes = $model->getAttribute(ColumnNames::JSON_ATTRIBUTES);
            $attributes = is_array($attributes) ? $attributes : [];

            if (! is_string($attributes[AvatarColor::ATTRIBUTE] ?? null)) {
                $attributes[AvatarColor::ATTRIBUTE] = $model->defaultAvatarColor()->value;
                $model->setAttribute(ColumnNames::JSON_ATTRIBUTES, $attributes);
            }
        });
    }

    public function avatarColor(): AvatarColor
    {
        $attributes = $this->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $stored = is_array($attributes) && is_string($attributes[AvatarColor::ATTRIBUTE] ?? null) ? AvatarColor::tryFrom($attributes[AvatarColor::ATTRIBUTE]) : null;

        return $stored ?? $this->defaultAvatarColor();
    }

    private function defaultAvatarColor(): AvatarColor
    {
        $email = $this->getAttribute(AccountColumns::EMAIL);

        return AvatarColor::forSeed(is_string($email) ? $email : '');
    }
}
