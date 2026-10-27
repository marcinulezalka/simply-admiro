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

namespace Simplysmart\Admiro\Translation;

use Illuminate\Translation\Translator;
use Illuminate\Support\Arr;

/**
 * Szablon tłumaczeń dla języka (np. do pobrania jako JSON w kreatorze onboardingu): wszystkie teksty plików PHP
 * każdej zarejestrowanej przestrzeni tłumaczeń (Core i paczki, `loadTranslationsFrom`) w języku źródłowym, klucz
 * `przestrzeń::plik.klucz`, z tekstem źródłowym i istniejącym tłumaczeniem w języku docelowym (brak — pusty tekst).
 * Teksty nie-tekstowe (np. listy liczb) są pomijane.
 */
final readonly class TranslationTemplate
{
    public function __construct(
        private Translator $translator,
    ) {}

    /**
     * @return array{language: string, source: string, texts: array<string, array{source: string, translation: string}>}
     */
    public function for(string $language, string $source): array
    {
        $texts = [];
        $loader = $this->translator->getLoader();

        foreach ($loader->namespaces() as $namespace => $path) {
            foreach (glob(rtrim($path, '/') . '/' . $source . '/*.php') ?: [] as $file) {
                $group = basename($file, '.php');
                $target = Arr::dot($loader->load($language, $group, $namespace));

                foreach (Arr::dot($loader->load($source, $group, $namespace)) as $key => $text) {
                    if (is_string($text)) {
                        $translation = $target[$key] ?? '';
                        $texts[$namespace . '::' . $group . '.' . $key] = ['source' => $text, 'translation' => is_string($translation) ? $translation : ''];
                    }
                }
            }
        }

        ksort($texts);

        return ['language' => $language, 'source' => $source, 'texts' => $texts];
    }
}
