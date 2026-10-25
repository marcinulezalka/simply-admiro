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

namespace Simplysmart\Mainboard\Database\Seeders;

use Illuminate\Config\Repository;
use Illuminate\Database\Seeder;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Cookies\CookiePolicies;
use Simplysmart\Mainboard\Enums\LegalDocumentType;
use Simplysmart\Mainboard\Legal\LegalDocuments;
use Simplysmart\Mainboard\Queries\Cookies\CookiePolicyVersions;
use Simplysmart\Mainboard\Queries\Legal\LegalDocumentVersions;

/**
 * Pierwsze wersje polityk: polityka cookies, polityka prywatności i klauzula RODO z plików Markdown
 * `database/seeders/legal/<język>/<dokument>.md` (pierwszy wiersz `# Tytuł`, reszta to treść) w każdym włączonym
 * języku interfejsu, który ma plik; `:name` i `:email` to administrator danych z `mainboard.legal.controller`,
 * `:consent_days` — ważność zgody cookies. Seeder referencyjny, idempotentny: dokument, który ma już wersję albo
 * szkic, zostaje bez zmian (kolejne wersje powstają w panelu administratora); bez pliku w języku domyślnym nic
 * nie jest publikowane.
 */
final class LegalDocumentsSeeder extends Seeder
{
    private const string CONTENT_PATH = __DIR__ . '/../../../database/seeders/legal';

    private const string COOKIES_FILE = 'cookies';

    public function __construct(
        private readonly Repository $config,
        private readonly InterfaceLocales $locales,
        private readonly CookiePolicies $cookiePolicies,
        private readonly CookiePolicyVersions $cookieVersions,
        private readonly LegalDocuments $documents,
        private readonly LegalDocumentVersions $documentVersions,
    ) {}

    public function run(): void
    {
        $default = $this->locales->default();
        $cookies = $this->content(self::COOKIES_FILE);

        if (isset($cookies[$default]) && $this->cookieVersions->current() === null && $this->cookieVersions->draft() === null) {
            $this->cookiePolicies->saveDraft($cookies);
            $this->cookiePolicies->publish($default);
        }

        foreach (LegalDocumentType::cases() as $type) {
            $content = $this->content($type->value);

            if (isset($content[$default]) && $this->documentVersions->current($type) === null && $this->documentVersions->draft($type) === null) {
                $this->documents->saveDraft($type, $content);
                $this->documents->publish($type, $default);
            }
        }
    }

    /**
     * @return array<string, array{title: string, body: string}> Język → tytuł i treść z pliku dokumentu.
     */
    private function content(string $file): array
    {
        $replacements = [
            ':name' => $this->config->string('mainboard.legal.controller.name'),
            ':email' => $this->config->string('mainboard.legal.controller.email'),
            ':consent_days' => (string) $this->config->integer('mainboard.cookies.lifetime_days'),
        ];
        $content = [];

        foreach ($this->locales->enabled() as $locale) {
            $path = self::CONTENT_PATH . "/{$locale}/{$file}.md";
            $markdown = is_file($path) ? file_get_contents($path) : false;

            if ($markdown === false) {
                continue;
            }

            [$heading, $body] = array_pad(explode("\n", strtr($markdown, $replacements), 2), 2, '');
            $content[$locale] = ['title' => trim(ltrim($heading, '# ')), 'body' => trim($body)];
        }

        return $content;
    }
}
