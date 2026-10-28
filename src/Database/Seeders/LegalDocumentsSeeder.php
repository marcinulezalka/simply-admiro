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

namespace Simplysmart\Admiro\Database\Seeders;

use Illuminate\Config\Repository;
use Illuminate\Database\Seeder;
use Psr\Log\LoggerInterface;
use Simplysmart\Admiro\Contracts\InterfaceLocales;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Legal\LegalDocuments;
use Simplysmart\Admiro\Queries\Legal\LegalDocumentVersions;

/**
 * Pierwsze wersje dokumentów prawnych (LegalDocumentType: polityka cookies, polityka prywatności, klauzula RODO)
 * z plików Markdown `database/seeders/legal/<język>/<dokument>.md` (pierwszy wiersz `# Tytuł`, reszta to treść)
 * w każdym włączonym języku interfejsu, który ma plik; `:name` i `:email` to administrator danych
 * z `admiro.legal.controller`, `:consent_days` — ważność zgody cookies. Seeder referencyjny, idempotentny: dokument, który ma już wersję albo
 * szkic, zostaje bez zmian (kolejne wersje powstają w panelu administratora); bez pliku w języku domyślnym nic
 * nie jest publikowane. Każdy dokument opublikowany z szablonu trafia do logu aplikacji (ostrzeżenie) i do wyjścia
 * komendy, żeby administrator sprawdził treść i dane administratora danych.
 */
final class LegalDocumentsSeeder extends Seeder
{
    private const string CONTENT_PATH = __DIR__ . '/../../../database/seeders/legal';

    public function __construct(
        private readonly Repository $config,
        private readonly InterfaceLocales $locales,
        private readonly LegalDocuments $documents,
        private readonly LegalDocumentVersions $documentVersions,
        private readonly LoggerInterface $logger,
    ) {}

    public function run(): void
    {
        $default = $this->locales->default();
        foreach (LegalDocumentType::cases() as $type) {
            $content = $this->content($type->value);

            if (isset($content[$default]) && $this->documentVersions->current($type) === null && $this->documentVersions->draft($type) === null) {
                $this->documents->saveDraft($type, $content);
                $this->documents->publish($type, $default);
                $this->reportTemplate($content[$default]['title']);
            }
        }
    }

    /**
     * Ostrzeżenie w logu aplikacji i w wyjściu komendy: dokument opublikowano z szablonu Core.
     */
    private function reportTemplate(string $title): void
    {
        $message = "Opublikowano szablon dokumentu „{$title}” z administratorem danych "
            . "{$this->config->string('admiro.legal.controller.name')} ({$this->config->string('admiro.legal.controller.email')}). "
            . 'To treść szablonowa: sprawdź ją i w razie potrzeby opublikuj nową wersję w panelu admiro.';

        $this->logger->warning($message);
        $this->command?->warn($message);
    }

    /**
     * @return array<string, array{title: string, body: string}> Język → tytuł i treść z pliku dokumentu.
     */
    private function content(string $file): array
    {
        $replacements = [
            ':name' => $this->config->string('admiro.legal.controller.name'),
            ':email' => $this->config->string('admiro.legal.controller.email'),
            ':consent_days' => (string) $this->config->integer('admiro.cookies.lifetime_days'),
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
