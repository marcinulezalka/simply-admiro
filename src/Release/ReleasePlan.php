<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Release;

/**
 * Wynik zaplanowanego wydania: poprzednia i nowa wersja oraz nowa treść CHANGELOG.md.
 */
final readonly class ReleasePlan
{
    public function __construct(
        public SemanticVersion $from,
        public SemanticVersion $to,
        public ReleaseBump $bump,
        public string $changelog,
    ) {}
}
