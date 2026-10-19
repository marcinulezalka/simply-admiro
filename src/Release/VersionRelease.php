<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Release;

/**
 * Wykonuje wydanie w katalogu celu: czyta pliki wydania, planuje wydanie i zapisuje wynik.
 */
final readonly class VersionRelease
{
    public function __construct(
        private ReleasePlanner $planner,
        private ReleaseClock $clock,
    ) {}

    /**
     * Planuje wydanie bez zapisywania plików (podgląd dla --dry-run).
     *
     * @param  ReleaseFiles  $files  Pliki wydania w katalogu celu.
     * @param  bool  $majorConfirmed  Czy użytkownik potwierdził podbicie MAJOR.
     * @return ReleasePlan|null Zaplanowane wydanie albo null, gdy w „[Unreleased]” nie ma wpisów.
     *
     * @throws ReleaseException Gdy wydania nie da się zaplanować.
     */
    public function plan(ReleaseFiles $files, bool $majorConfirmed): ?ReleasePlan
    {
        return $this->planner->plan($files->changelog(), $files->version(), $this->clock->now(), $majorConfirmed);
    }

    /**
     * @param  ReleaseFiles  $files  Pliki wydania w katalogu celu.
     * @param  bool  $majorConfirmed  Czy użytkownik potwierdził podbicie MAJOR.
     * @return ReleasePlan|null Wykonane wydanie albo null, gdy w „[Unreleased]” nie ma wpisów.
     *
     * @throws ReleaseException Gdy wydania nie da się zaplanować albo zapisać.
     */
    public function release(ReleaseFiles $files, bool $majorConfirmed): ?ReleasePlan
    {
        $plan = $this->plan($files, $majorConfirmed);

        if ($plan !== null) {
            $files->save($plan);
        }

        return $plan;
    }
}
