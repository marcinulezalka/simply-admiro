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

namespace Simplysmart\Mainboard\Legal;

use Illuminate\Contracts\Routing\UrlGenerator;
use Simplysmart\Mainboard\Enums\LegalDocumentType;
use Simplysmart\Mainboard\Queries\Cookies\CookiePolicyVersions;
use Simplysmart\Mainboard\Queries\Legal\LegalDocumentVersions;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Odnośniki prawne: w strefie (karta logowania, stopka layoutu aplikacji, e-maile) opublikowane dokumenty Core
 * z `legal_documents` strefy, potem jej własne `legal_links`; na stronach dokumentów karta „Zobacz także”
 * z pozostałymi opublikowanymi dokumentami i polityką cookies. Nieopublikowany dokument nie ma odnośnika.
 */
final readonly class LegalLinks
{
    public const string COOKIES = 'cookies';

    public function __construct(
        private LegalDocumentVersions $documents,
        private CookiePolicyVersions $cookies,
        private UrlGenerator $url,
    ) {}

    /**
     * @param array<string, string> $parameters Parametry tras (np. język w adresach e-maili).
     *
     * @return list<array{url: string, label: string}>
     */
    public function for(ZoneDefinition $zone, array $parameters = []): array
    {
        $published = $zone->legalDocuments === [] ? [] : $this->documents->publishedTypes();
        $links = [];

        foreach ($zone->legalDocuments as $type) {
            if (in_array($type, $published, true)) {
                $links[] = ['url' => $this->url->route($type->routeName(), $parameters), 'label' => $type->label()];
            }
        }

        foreach ($zone->legalLinks as $link) {
            $links[] = ['url' => $this->url->route($zone->routeName($link['route']), $parameters), 'label' => text($link['label'])];
        }

        return $links;
    }

    /**
     * Pozostałe opublikowane dokumenty (bez `$current`: wartość LegalDocumentType albo self::COOKIES).
     *
     * @return list<array{url: string, title: string, text: string, icon: string}>
     */
    public function related(string $current): array
    {
        $related = [];

        foreach ($this->documents->publishedTypes() as $type) {
            if ($type->value !== $current) {
                $related[] = ['url' => $this->url->route($type->routeName()), 'title' => $type->label(), 'text' => $type->lead(), 'icon' => $type->icon()];
            }
        }

        if ($current !== self::COOKIES && $this->cookies->current() !== null) {
            $related[] = ['url' => $this->url->route('mainboard.cookies.policy'), 'title' => text('cookies.links.policy'), 'text' => text('legal.cookies_lead'), 'icon' => 'cookie'];
        }

        return $related;
    }
}
