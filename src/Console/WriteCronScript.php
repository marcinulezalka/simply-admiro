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

namespace Simplysmart\Admiro\Console;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;

/**
 * Tworzy skrypt crona `storage/app/cron.sh` z szablonu `resources/stubs/cron.sh.stub`: harmonogram Laravela
 * i worker kolejki dla hostingu bez supervisora, którego panel uruchamia tylko plik (program BASH).
 *
 * Wartości z `admiro.cron.*` (zmienne CRON_* w .env) i katalog aplikacji są wstawiane przy generowaniu,
 * więc skrypt nie czyta zmiennych środowiska i nie zawiera sekretów. Worker kończy pracę 10 s przed kolejnym uruchomieniem; przy
 * interwale 1 min wychodzi, gdy kolejka jest pusta, przy dłuższym czeka na zadania. Bez workera, gdy
 * `queue_worker` jest wyłączony albo kolejka to `sync`. Wywoływany przy każdym wdrożeniu (`--php` z wdrożenia).
 */
final class WriteCronScript extends Command
{
    /**
     * Polecenie PHP: nazwa albo ścieżka binarki, bez spacji i znaków powłoki.
     */
    private const string PHP_PATTERN = '/^[A-Za-z0-9._\/-]+$/';

    /**
     * Ile sekund przed kolejnym uruchomieniem crona worker kończy pracę.
     */
    private const int WORKER_MARGIN_SECONDS = 10;

    protected $signature = 'admiro:cron:script
        {--php= : Polecenie PHP na serwerze (domyślnie admiro.cron.php)}
        {--output= : Ścieżka pliku (domyślnie storage/app/cron.sh)}';

    protected $description = 'Tworzy skrypt crona (harmonogram i worker kolejki) dla hostingu bez supervisora';

    public function handle(Repository $config): int
    {
        $option = $this->option('php');
        $php = is_string($option) && $option !== '' ? $option : $config->string('admiro.cron.php');
        $interval = $config->integer('admiro.cron.interval_minutes');
        $tries = $config->integer('admiro.cron.queue_tries');

        if (preg_match(self::PHP_PATTERN, $php) !== 1 || $interval < 1 || $tries < 1) {
            $this->error('Nieprawidłowa konfiguracja: CRON_PHP to nazwa albo ścieżka PHP, CRON_INTERVAL_MINUTES i CRON_QUEUE_TRIES ≥ 1.');

            return self::FAILURE;
        }

        $worker = $config->boolean('admiro.cron.queue_worker') && $config->string('queue.default') !== 'sync'
            ? $this->workerLine($php, $interval, $tries)
            : '# Worker kolejki wyłączony (CRON_QUEUE_WORKER=false albo QUEUE_CONNECTION=sync).';

        $output = $this->option('output');
        $path = is_string($output) && $output !== '' ? $output : storage_path('app/cron.sh');
        $stub = file_get_contents(__DIR__ . '/../../resources/stubs/cron.sh.stub');

        if ($stub === false || ! is_dir(dirname($path)) || file_put_contents($path, strtr($stub, [
            '{{app_dir}}' => escapeshellarg(base_path()),
            '{{php}}' => $php,
            '{{interval}}' => (string) $interval,
            '{{worker}}' => $worker,
        ])) === false || ! chmod($path, 0755)) {
            $this->error("Nie zapisano skryptu crona: {$path}");

            return self::FAILURE;
        }

        $this->info("Skrypt crona: {$path}");
        $this->line("Panel hostingu: program BASH, plik {$path}, interwał co {$interval} min.");

        return self::SUCCESS;
    }

    private function workerLine(string $php, int $interval, int $tries): string
    {
        $maxTime = $interval * 60 - self::WORKER_MARGIN_SECONDS;
        $stopWhenEmpty = $interval === 1 ? ' --stop-when-empty' : '';

        return "{$php} artisan queue:work{$stopWhenEmpty} --max-time={$maxTime} --tries={$tries}";
    }
}
