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

namespace Simplysmart\Mainboard\Translation;

use Illuminate\Support\Str;
use Illuminate\Translation\Translator;
use Simplysmart\Mainboard\Contracts\TextTranslator;

/**
 * Kaskada tłumaczeń: paczka strefy (z nadpisaniem lang/vendor/<paczka>/) → mainboard → skeleton.
 *
 * Każdy krok pyta tylko o bieżący język (bez języka zapasowego); gdy klucza nie ma nigdzie,
 * zwraca klucz w postaci, w jakiej go podano.
 */
final readonly class CascadeTextTranslator implements TextTranslator
{
    public const string CORE_NAMESPACE = 'mainboard';

    private const string NAMESPACE_SEPARATOR = '::';

    public function __construct(
        private Translator $translator,
    ) {}

    public function text(string $key, array $replace = [], ?string $locale = null): string
    {
        foreach ($this->candidates($key) as $candidate) {
            $line = $this->translator->get($candidate, [], $locale, false);

            if (is_string($line) && $line !== $candidate) {
                $translated = $this->translator->get($candidate, $replace, $locale, false);

                return is_string($translated) ? $translated : $line;
            }
        }

        return $key;
    }

    public function group(TextGroup $group, string $key, array $replace = [], ?string $locale = null): string
    {
        return $this->text($this->prefixed($group, $key), $replace, $locale);
    }

    public function data(object|string $model, string $column, array $replace = [], ?string $locale = null): string
    {
        [$namespace, $name] = $this->split(is_object($model) ? $model::class : $model);
        $modelKey = $this->join($namespace, Str::snake(class_basename($name)) . '.' . $column);
        $specific = $this->prefixed(TextGroup::Data, $modelKey);
        $translated = $this->text($specific, $replace, $locale);

        if ($translated !== $specific) {
            return $translated;
        }

        $shared = $this->prefixed(TextGroup::Data, $this->join($namespace, $column));
        $translated = $this->text($shared, $replace, $locale);

        return $translated !== $shared ? $translated : $specific;
    }

    /**
     * Klucze do sprawdzenia po kolei: paczka (gdy podano namespace), mainboard, skeleton.
     *
     * @return list<string>
     */
    private function candidates(string $key): array
    {
        [$namespace, $item] = $this->split($key);
        $core = $this->join(self::CORE_NAMESPACE, $item);

        return array_values(array_unique(array_filter([
            $namespace !== null ? $key : null,
            $core,
            $item,
        ], is_string(...))));
    }

    /**
     * Wstawia grupę po namespace: "zone::welcome" + Emails → "zone::emails.welcome".
     */
    private function prefixed(TextGroup $group, string $key): string
    {
        [$namespace, $item] = $this->split($key);

        return $this->join($namespace, $group->value . '.' . $item);
    }

    /**
     * @return array{0: string|null, 1: string} namespace (lub null) i reszta klucza
     */
    private function split(string $key): array
    {
        if (! str_contains($key, self::NAMESPACE_SEPARATOR)) {
            return [null, $key];
        }

        [$namespace, $item] = explode(self::NAMESPACE_SEPARATOR, $key, 2);

        return [$namespace, $item];
    }

    private function join(?string $namespace, string $item): string
    {
        return $namespace === null ? $item : $namespace . self::NAMESPACE_SEPARATOR . $item;
    }
}
