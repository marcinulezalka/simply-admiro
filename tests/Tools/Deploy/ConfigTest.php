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

namespace Simplysmart\Admiro\Tests\Tools\Deploy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Deploy\DeployConfig;
use Simplysmart\Admiro\Tools\Deploy\DeployConfigException;
use Simplysmart\Admiro\Tools\Deploy\MissingConfigException;
use Simplysmart\Admiro\Tools\Deploy\OnboardingConfig;
use Simplysmart\Admiro\Tools\Deploy\ProjectConfig;

/**
 * Konfiguracja wdrożenia (deploy.env), projektu (deploy-vendors/<projekt>.env) i onboardingu (deploy.onboarding.env)
 * z wartości pliku.
 */
final class ConfigTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/deploy-env-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }

        if (is_dir($this->file)) {
            exec('rm -rf ' . escapeshellarg($this->file));
        }
    }

    #[Test]
    public function czyta_konfiguracje_wdrozenia_z_domyslnymi(): void
    {
        $config = $this->deployConfig("DEPLOY_SSH=user@host\nDEPLOY_PATH = ~/app.pl\nDEPLOY_PHP=\nLOCAL_COMPOSER=\"php  composer.phar\"\nGITHUB_TOKEN=\n");

        $this->assertSame('user@host', $config->ssh);
        $this->assertSame('~/app.pl', $config->path);
        $this->assertSame('php84', $config->php);
        $this->assertNull($config->remoteComposer);
        $this->assertSame(['php', 'composer.phar'], $config->localComposer);
        $this->assertNull($config->githubToken);
    }

    #[Test]
    public function brak_wymaganych_wartosci_wymienia_klucze(): void
    {
        $this->expectException(MissingConfigException::class);
        $this->expectExceptionMessage('Brak wymaganych wartości w deploy.env: DEPLOY_SSH, DEPLOY_PATH. Wzór: deploy.env.example.');

        $this->deployConfig("DEPLOY_SSH=\nDEPLOY_PHP=php84\n");
    }

    #[Test]
    public function czyta_konfiguracje_projektu_z_przed_produkcja_i_produkcja(): void
    {
        $root = $this->projectRoot(['shop.env' => "PREPRODUCTION_PATH=/var/www/shop-staging/\nDEPLOY_SSH=shop@prod\nDEPLOY_PATH=~/shop.pl\nDEPLOY_PHP=php84\n", 'shop.onboarding.env' => '', 'blog.env' => '']);

        $config = ProjectConfig::load($root, 'shop');

        $this->assertSame('/var/www/shop-staging', $config->preproductionPath);
        $this->assertSame('php', $config->preproductionPhp);
        $this->assertNull($config->preproductionComposer);
        $this->assertSame('shop@prod', $config->production->ssh);
        $this->assertSame('~/shop.pl', $config->production->path);
        $this->assertSame('infrastructure/deploy-vendors/shop.onboarding.env', $config->onboardingFile());
        $this->assertSame(['blog', 'shop'], ProjectConfig::available($root));
    }

    #[Test]
    public function projekt_bez_przed_produkcji_albo_ze_sciezka_wzgledna_to_blad(): void
    {
        $root = $this->projectRoot(['shop.env' => "DEPLOY_SSH=shop@prod\nDEPLOY_PATH=~/shop.pl\n", 'blog.env' => "PREPRODUCTION_PATH=blog\nDEPLOY_SSH=a\nDEPLOY_PATH=b\n"]);

        try {
            ProjectConfig::load($root, 'shop');
            $this->fail('Brak PREPRODUCTION_PATH powinien być błędem.');
        } catch (MissingConfigException $exception) {
            $this->assertSame('Brak wymaganych wartości w infrastructure/deploy-vendors/shop.env: PREPRODUCTION_PATH. Wzór: infrastructure/deploy-vendor.env.example.', $exception->getMessage());
        }

        $this->expectExceptionMessage('infrastructure/deploy-vendors/blog.env: PREPRODUCTION_PATH musi być ścieżką bezwzględną.');
        ProjectConfig::load($root, 'blog');
    }

    #[Test]
    public function czyta_konta_onboardingu_w_kolejnosci_numerow(): void
    {
        $accounts = OnboardingConfig::fromValues([
            'ONBOARD_ACCOUNT_2_ZONE' => 'shop',
            'ONBOARD_ACCOUNT_2_EMAIL' => 'b@example.com',
            'ONBOARD_ACCOUNT_2_NAME' => "Anna O'Brien",
            'ONBOARD_ACCOUNT_1_ZONE' => 'admiro',
            'ONBOARD_ACCOUNT_1_EMAIL' => 'a@example.com',
            'ONBOARD_ACCOUNT_1_NAME' => 'Jan Kowalski',
            'ONBOARD_ACCOUNT_1_ROLES' => 'hero, , auditor',
            'ONBOARD_ACCOUNT_1_ORGANIZATION' => 'org-token',
        ], 'onboarding.env')->accounts;

        $this->assertCount(2, $accounts);
        $this->assertSame(
            ['admiro:account:create', 'a@example.com', 'Jan Kowalski', '--role=hero', '--role=auditor', '--organization=org-token', '--skip-existing'],
            $accounts[0]->artisanArguments(),
        );
        $this->assertSame(['shop:account:create', 'b@example.com', "Anna O'Brien", '--skip-existing'], $accounts[1]->artisanArguments());
    }

    #[Test]
    public function konto_bez_wymaganych_pol_wymienia_klucze(): void
    {
        $this->expectException(MissingConfigException::class);
        $this->expectExceptionMessage('ONBOARD_ACCOUNT_1_EMAIL, ONBOARD_ACCOUNT_1_NAME');

        OnboardingConfig::fromValues(['ONBOARD_ACCOUNT_1_ZONE' => 'admiro'], 'onboarding.env');
    }

    #[Test]
    public function odrzuca_nieznany_klucz(): void
    {
        $this->expectException(DeployConfigException::class);
        $this->expectExceptionMessage('nieznany klucz ONBOARD_ACCOUNT_1_MAIL');

        OnboardingConfig::fromValues(['ONBOARD_ACCOUNT_1_MAIL' => 'a@example.com'], 'onboarding.env');
    }

    #[Test]
    public function odrzuca_strefe_spoza_nazwy_polecenia(): void
    {
        $this->expectException(DeployConfigException::class);
        $this->expectExceptionMessage('ONBOARD_ACCOUNT_1_ZONE');

        OnboardingConfig::fromValues([
            'ONBOARD_ACCOUNT_1_ZONE' => 'admiro; rm -rf ~',
            'ONBOARD_ACCOUNT_1_EMAIL' => 'a@example.com',
            'ONBOARD_ACCOUNT_1_NAME' => 'Jan',
        ], 'onboarding.env');
    }

    #[Test]
    public function pusty_onboarding_to_blad(): void
    {
        $this->expectException(DeployConfigException::class);
        $this->expectExceptionMessage('brak kont');

        OnboardingConfig::fromValues([], 'onboarding.env');
    }

    /**
     * Katalog projektu bazowego z plikami w `infrastructure/deploy-vendors/` (usuwany w tearDown razem z $this->file).
     *
     * @param  array<string, string>  $files
     */
    private function projectRoot(array $files): string
    {
        mkdir($this->file . '/' . ProjectConfig::DIRECTORY, recursive: true);

        foreach ($files as $name => $contents) {
            file_put_contents($this->file . '/' . ProjectConfig::DIRECTORY . "/{$name}", $contents);
        }

        return $this->file;
    }

    private function deployConfig(string $contents): DeployConfig
    {
        file_put_contents($this->file, $contents);

        return DeployConfig::fromFile($this->file, 'deploy.env');
    }
}
