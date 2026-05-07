<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Metadata;

use Bitrix24\SDK\Infrastructure\Console\Commands\Metadata\DevWebhookResolver;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DevWebhookResolverTest extends TestCase
{
    /**
     * @var array<string, string|null>
     */
    private array $originalEnvironment = [];

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->getEnvNames() as $envName) {
            $value = getenv($envName);
            $this->originalEnvironment[$envName] = $value === false ? null : $value;
            $this->writeEnv($envName, null);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->originalEnvironment as $envName => $value) {
            $this->writeEnv($envName, $value);
        }

        parent::tearDown();
    }

    #[Test]
    public function itUsesExplicitWebhookBeforeEnvironmentVariables(): void
    {
        $this->writeEnv('BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK', 'https://playground.example/rest/1/token/');
        $this->writeEnv('BITRIX24_WEBHOOK', 'https://fallback.example/rest/1/token/');

        $webhook = (new DevWebhookResolver())->resolve('  https://cli.example/rest/1/token/  ');

        $this->assertSame('https://cli.example/rest/1/token/', $webhook);
    }

    #[Test]
    public function itUsesPlaygroundWebhookBeforeDefaultWebhook(): void
    {
        $this->writeEnv('BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK', 'https://playground.example/rest/1/token/');
        $this->writeEnv('BITRIX24_WEBHOOK', 'https://fallback.example/rest/1/token/');

        $webhook = (new DevWebhookResolver())->resolve(null);

        $this->assertSame('https://playground.example/rest/1/token/', $webhook);
    }

    #[Test]
    public function itFallsBackToDefaultWebhookWhenPlaygroundWebhookIsMissing(): void
    {
        $this->writeEnv('BITRIX24_WEBHOOK', 'https://fallback.example/rest/1/token/');

        $webhook = (new DevWebhookResolver())->resolve('');

        $this->assertSame('https://fallback.example/rest/1/token/', $webhook);
    }

    #[Test]
    public function itRejectsMissingWebhookConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Webhook is not configured. Pass --webhook or set BITRIX24_WEBHOOK in tests/.env.local'
        );

        (new DevWebhookResolver())->resolve(null);
    }

    /**
     * @return list<string>
     */
    private function getEnvNames(): array
    {
        return ['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK', 'BITRIX24_WEBHOOK'];
    }

    private function writeEnv(string $envName, ?string $value): void
    {
        if ($value === null) {
            unset($_ENV[$envName], $_SERVER[$envName]);
            putenv($envName);

            return;
        }

        $_ENV[$envName] = $value;
        $_SERVER[$envName] = $value;
        putenv(sprintf('%s=%s', $envName, $value));
    }
}
