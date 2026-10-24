<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/tools.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Tools\Tests\Deploy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Tools\Deploy\DeployConfig;
use Simplysmart\Tools\Deploy\DeployConfigException;
use Simplysmart\Tools\Deploy\MissingConfigException;
use Simplysmart\Tools\Deploy\OnboardingConfig;

/**
 * Konfiguracja wdrożenia (deploy.env) i onboardingu (deploy.onboarding.env) z wartości pliku.
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

    private function deployConfig(string $contents): DeployConfig
    {
        file_put_contents($this->file, $contents);

        return DeployConfig::fromFile($this->file, 'deploy.env');
    }
}
