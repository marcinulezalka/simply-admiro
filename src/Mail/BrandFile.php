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

namespace Simplysmart\Mainboard\Mail;

use RuntimeException;
use Simplysmart\Mainboard\Enums\BrandAsset;

/**
 * Plik marki gotowy do osadzenia w e-mailu (CID) albo podglądu: zawartość, typ MIME i nazwa.
 */
final readonly class BrandFile
{
    public function __construct(
        public string $contents,
        public string $mimeType,
        public string $name,
    ) {}

    /**
     * @throws RuntimeException Gdy pliku nie da się odczytać.
     */
    public static function fromPath(string $path): self
    {
        $contents = is_file($path) ? file_get_contents($path) : false;

        if ($contents === false) {
            throw new RuntimeException("Brand file [{$path}] cannot be read.");
        }

        return new self($contents, BrandAsset::mimeType(pathinfo($path, PATHINFO_EXTENSION)), basename($path));
    }

    /**
     * Adres `data:` do podglądu poza wysyłką (render maila bez wiadomości).
     */
    public function dataUri(): string
    {
        return "data:{$this->mimeType};base64," . base64_encode($this->contents);
    }
}
