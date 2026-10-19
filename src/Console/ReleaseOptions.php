<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Console;

use Simplysmart\Tools\Release\ReleaseException;

/**
 * Opcje wywołania vendor/bin/version-release z linii poleceń.
 */
final readonly class ReleaseOptions
{
    public const string USAGE = 'Użycie: vendor/bin/version-release [--root=<katalog>] [--major-confirm] [--dry-run]';

    private const string OPTION_ROOT = '--root=';

    private const string OPTION_MAJOR_CONFIRM = '--major-confirm';

    private const string OPTION_DRY_RUN = '--dry-run';

    /**
     * @param  string  $root  Katalog celu wydania z plikami CHANGELOG.md i VERSION.
     * @param  bool  $majorConfirmed  Czy użytkownik potwierdził podbicie MAJOR.
     * @param  bool  $dryRun  Czy tylko pokazać plan wydania bez zapisywania plików.
     */
    public function __construct(
        public string $root,
        public bool $majorConfirmed,
        public bool $dryRun = false,
    ) {}

    /**
     * Odczytuje opcje z argumentów linii poleceń.
     *
     * @param  list<string>  $arguments  Argumenty bez nazwy skryptu.
     * @param  string  $defaultRoot  Katalog celu wydania, gdy nie podano --root.
     * @return self Odczytane opcje; względny --root liczony jest od bieżącego katalogu.
     *
     * @throws ReleaseException Gdy podano nieznaną opcję albo --root nie wskazuje istniejącego katalogu.
     */
    public static function fromArguments(array $arguments, string $defaultRoot): self
    {
        $root = $defaultRoot;
        $majorConfirmed = false;
        $dryRun = false;

        foreach ($arguments as $argument) {
            if ($argument === self::OPTION_MAJOR_CONFIRM) {
                $majorConfirmed = true;
            } elseif ($argument === self::OPTION_DRY_RUN) {
                $dryRun = true;
            } elseif (str_starts_with($argument, self::OPTION_ROOT)) {
                $root = substr($argument, strlen(self::OPTION_ROOT));
            } else {
                throw new ReleaseException("Nieznana opcja: {$argument}. " . self::USAGE);
            }
        }

        $resolved = $root !== '' ? realpath($root) : false;

        if ($resolved === false || ! is_dir($resolved)) {
            throw new ReleaseException("Katalog celu wydania nie istnieje: „{$root}”. " . self::USAGE);
        }

        return new self($resolved, $majorConfirmed, $dryRun);
    }
}
