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

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use RuntimeException;

/**
 * Import pliku tłumaczeń w formacie szablonu (TranslationTemplate: `texts` → `przestrzeń::plik.klucz` →
 * `translation`) do nadpisań aplikacji `lang/vendor/<przestrzeń>/<język>/<plik>.php`, które Laravel scala z tekstami
 * paczki. Przyjmuje tylko klucze szablonu (znane przestrzenie i pliki, więc ścieżki są bezpieczne) z niepustym
 * tłumaczeniem; istniejące nadpisania zostają, a te same klucze dostają nowe teksty. Pliki zapisuje atomowo.
 */
final readonly class TranslationImport
{
    /** Najwięcej plików w jednym przesłaniu. */
    public const int MAX_FILES = 10;

    /** Największy przesyłany plik (KB). */
    public const int MAX_KILOBYTES = 5120;

    public function __construct(
        private TranslationTemplate $template,
        private Application $app,
    ) {}

    /**
     * @param array<mixed> $texts Sekcja `texts` przesłanego pliku.
     *
     * @return int Liczba zapisanych tłumaczeń.
     */
    public function import(string $language, string $source, array $texts): int
    {
        $known = $this->template->for($language, $source)['texts'];
        $groups = [];

        foreach ($texts as $key => $text) {
            $translation = is_array($text) ? ($text['translation'] ?? null) : $text;

            if (! is_string($key) || ! array_key_exists($key, $known) || ! is_string($translation) || trim($translation) === '') {
                continue;
            }

            [$namespace, $item] = explode('::', $key, 2);
            [$group, $line] = explode('.', $item, 2);
            $groups[$namespace][$group][$line] = $translation;
        }

        $count = 0;

        foreach ($groups as $namespace => $files) {
            foreach ($files as $group => $lines) {
                $this->write($this->app->langPath('vendor/' . $namespace . '/' . $language . '/' . $group . '.php'), $lines);
                $count += count($lines);
            }
        }

        return $count;
    }

    /**
     * @param array<string, string> $lines Teksty z kluczami w notacji kropkowej.
     */
    private function write(string $path, array $lines): void
    {
        $existing = is_file($path) ? require $path : [];
        $merged = array_replace_recursive(is_array($existing) ? $existing : [], Arr::undot($lines));
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0o775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Cannot create the translations directory ' . $directory);
        }

        $temporary = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($temporary, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($merged, true) . ";\n");
        rename($temporary, $path);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }
}
