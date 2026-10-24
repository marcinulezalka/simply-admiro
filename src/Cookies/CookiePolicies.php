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

namespace Simplysmart\Mainboard\Cookies;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\CookiePolicy;
use Simplysmart\Mainboard\Queries\Cookies\CookiePolicyVersions;

/**
 * Polityka cookies w wersjach: jeden szkic (zaczyna się od treści bieżącej wersji), zapis tytułu i treści Markdown
 * w językach interfejsu i publikacja szkicu jako kolejnej wersji (wymaga treści w języku domyślnym). Opublikowanej
 * wersji się nie zmienia.
 */
final readonly class CookiePolicies
{
    public function __construct(
        private CookiePolicyVersions $versions,
        private ConnectionInterface $db,
    ) {}

    /**
     * Szkic polityki: istniejący albo nowy z treścią bieżącej wersji.
     */
    public function draft(): CookiePolicy
    {
        $draft = $this->versions->draft();

        if ($draft !== null) {
            return $draft;
        }

        $draft = new CookiePolicy();
        $draft->forceFill([
            ColumnNames::NAME => text('cookies.policy.draft_name'),
            ColumnNames::IS_ACTIVE => true,
            ColumnNames::JSON_I18N => $this->versions->current()?->getAttribute(ColumnNames::JSON_I18N) ?? [],
        ])->save();

        return $draft;
    }

    /**
     * @param array<string, array{title: string, body: string}> $content Język → tytuł i treść Markdown.
     */
    public function saveDraft(array $content): CookiePolicy
    {
        $draft = $this->draft();
        $current = $draft->getAttribute(ColumnNames::JSON_I18N);
        $draft->forceFill([ColumnNames::JSON_I18N => [...(is_array($current) ? $current : []), ...$content]])->save();

        return $draft;
    }

    /**
     * @throws CookiePolicyRejected Gdy nie ma szkicu albo szkic nie ma tytułu i treści w języku `$defaultLocale`.
     */
    public function publish(string $defaultLocale): CookiePolicy
    {
        $draft = $this->versions->draft() ?? throw CookiePolicyRejected::because('there is no draft to publish.');
        $content = $draft->content($defaultLocale);

        if (trim($content['title']) === '' || trim($content['body']) === '') {
            throw CookiePolicyRejected::because('the draft needs a title and content in the default language.');
        }

        return $this->db->transaction(function () use ($draft): CookiePolicy {
            $version = ($this->versions->current()->version ?? 0) + 1;
            $draft->forceFill([
                ColumnNames::NAME => text('cookies.policy.version_name', ['version' => (string) $version]),
                'version' => $version,
                'published_at' => Carbon::now(),
            ])->save();

            return $draft;
        });
    }
}
