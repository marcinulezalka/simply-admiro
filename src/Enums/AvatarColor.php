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
 * Kolor awatara konta (tokeny `--ss-avatar-<kolor>-bg` / `-fg`, jasny i ciemny motyw, kontrast co najmniej 6,4:1).
 * Nowe konto dostaje kolor wyliczony z adresu e-mail (stały, bez losowania), zapisany w `json_attributes`
 * pod {@see self::ATTRIBUTE}; użytkownik zmienia go w profilu.
 */
enum AvatarColor: string
{
    public const string ATTRIBUTE = 'avatar_color';

    case Slate = 'slate';
    case Red = 'red';
    case Amber = 'amber';
    case Green = 'green';
    case Teal = 'teal';
    case Blue = 'blue';
    case Violet = 'violet';
    case Pink = 'pink';

    /**
     * Kolor wyliczony z wartości (np. adresu e-mail): ten sam dla tej samej wartości, różne wartości rozkładają się
     * po palecie.
     */
    public static function forSeed(string $seed): self
    {
        $cases = self::cases();

        return $cases[crc32(mb_strtolower(trim($seed))) % count($cases)];
    }

    public function label(): string
    {
        return text('account.avatar_colors.' . $this->value);
    }
}
