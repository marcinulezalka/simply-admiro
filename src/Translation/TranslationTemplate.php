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
 * Teksty nie-tekstowe (np. listy liczb) są pomijane. Wypełniony szablon wraca przez TranslationImport.
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

    /**
     * Pusty szablon do uzupełnienia nowym językiem: teksty języka źródłowego z pustymi tłumaczeniami i puste pole
     * `language` (użytkownik wpisuje kod włączonego języka, uzupełnia `translation` i wgrywa plik).
     *
     * @return array{language: string, source: string, texts: array<string, array{source: string, translation: string}>}
     */
    public function blank(string $source): array
    {
        $template = $this->for($source, $source);

        return [
            'language' => '',
            'source' => $source,
            'texts' => array_map(static fn(array $text): array => ['source' => $text['source'], 'translation' => ''], $template['texts']),
        ];
    }

    /**
     * Licznik fraz językowych: klucze szablonu z niepustym tekstem źródłowym, te z nich z niepustym tłumaczeniem i procent uzupełnienia względem fraz języka źródłowego (z jednym miejscem po przecinku, w dół — kilka fraz nie daje 0%, a niepełny komplet nie daje 100%).
     *
     * @return array{translated: int, all: int, percent: float}
     */
    public function counts(string $language, string $source): array
    {
        $texts = array_filter($this->for($language, $source)['texts'], static fn(array $text): bool => trim($text['source']) !== '');

        $translated = count(array_filter($texts, static fn(array $text): bool => trim($text['translation']) !== ''));

        return [
            'translated' => $translated,
            'all' => count($texts),
            'percent' => $texts === [] ? 0.0 : floor($translated * 1000 / count($texts)) / 10,
        ];
    }
}
