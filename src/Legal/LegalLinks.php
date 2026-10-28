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

namespace Simplysmart\Admiro\Legal;

use Illuminate\Contracts\Routing\UrlGenerator;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Queries\Legal\LegalDocumentVersions;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Odnośniki prawne: w strefie (karta logowania, stopka layoutu aplikacji, e-maile) opublikowane dokumenty Core
 * z `legal_documents` strefy, potem jej własne `legal_links`; na stronach dokumentów (także polityki cookies) karta
 * „Zobacz także” z pozostałymi opublikowanymi dokumentami. Nieopublikowany dokument nie ma odnośnika.
 */
final readonly class LegalLinks
{
    public function __construct(
        private LegalDocumentVersions $documents,
        private UrlGenerator $url,
    ) {}

    /**
     * @param array<string, string> $parameters Parametry tras (np. język w adresach e-maili).
     *
     * @return list<array{url: string, label: string, key: string}> `key`: wartość LegalDocumentType albo nazwa trasy strefy.
     */
    public function for(ZoneDefinition $zone, array $parameters = []): array
    {
        $published = $zone->legalDocuments === [] ? [] : $this->documents->publishedTypes();
        $links = [];

        foreach ($zone->legalDocuments as $type) {
            if (in_array($type, $published, true)) {
                $links[] = ['url' => $this->url->route($type->routeName(), $parameters), 'label' => $type->label(), 'key' => $type->value];
            }
        }

        foreach ($zone->legalLinks as $link) {
            $links[] = ['url' => $this->url->route($zone->routeName($link['route']), $parameters), 'label' => text($link['label']), 'key' => $link['route']];
        }

        return $links;
    }

    /**
     * Pozostałe opublikowane dokumenty (bez `$current`).
     *
     * @return list<array{url: string, title: string, text: string, icon: string}>
     */
    public function related(LegalDocumentType $current): array
    {
        $related = [];

        foreach ($this->documents->publishedTypes() as $type) {
            if ($type !== $current) {
                $related[] = ['url' => $this->url->route($type->routeName()), 'title' => $type->label(), 'text' => $type->lead(), 'icon' => $type->icon()];
            }
        }

        return $related;
    }
}
