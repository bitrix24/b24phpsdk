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

namespace Bitrix24\SDK\Tests\Unit\Core\Credentials;

use Bitrix24\SDK\Core\Credentials\DefaultOAuthServerUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultOAuthServerUrl::class)]
class DefaultOAuthServerUrlTest extends TestCase
{
    private const ENV_VAR_NAME = 'BITRIX24_PHP_SDK_DEFAULT_AUTH_SERVER_URL';

    protected function setUp(): void
    {
        parent::setUp();
        // Clean up environment variable before each test
        if (isset($_ENV[self::ENV_VAR_NAME])) {
            unset($_ENV[self::ENV_VAR_NAME]);
        }
    }

    protected function tearDown(): void
    {
        // Clean up environment variable after each test
        if (isset($_ENV[self::ENV_VAR_NAME])) {
            unset($_ENV[self::ENV_VAR_NAME]);
        }

        parent::tearDown();
    }

    #[Test]
    #[TestDox('tests east() returns correct OAuth server URL for east region')]
    public function testEastReturnsCorrectUrl(): void
    {
        $this->assertEquals(
            'https://oauth.bitrix24.tech/',
            DefaultOAuthServerUrl::east()
        );
    }

    #[Test]
    #[TestDox('tests west() returns correct OAuth server URL for west region')]
    public function testWestReturnsCorrectUrl(): void
    {
        $this->assertEquals(
            'https://oauth.bitrix.info/',
            DefaultOAuthServerUrl::west()
        );
    }

    #[Test]
    #[TestDox('tests default() returns west URL when environment variable is not set')]
    public function testDefaultReturnsWestUrlWhenEnvVariableNotSet(): void
    {
        $this->assertEquals(
            DefaultOAuthServerUrl::west(),
            DefaultOAuthServerUrl::default()
        );
    }

    #[Test]
    #[TestDox('tests default() returns environment variable value when it is set')]
    public function testDefaultReturnsEnvVariableValueWhenSet(): void
    {
        $customUrl = 'https://custom.oauth.server/';
        $_ENV[self::ENV_VAR_NAME] = $customUrl;

        $this->assertEquals(
            $customUrl,
            DefaultOAuthServerUrl::default()
        );
    }

    #[Test]
    #[TestDox('tests default() returns west URL when environment variable is empty string')]
    public function testDefaultReturnsWestUrlWhenEnvVariableIsEmpty(): void
    {
        $_ENV[self::ENV_VAR_NAME] = '';

        $this->assertEquals(
            DefaultOAuthServerUrl::west(),
            DefaultOAuthServerUrl::default()
        );
    }

    #[Test]
    #[TestDox('tests default() returns west URL when environment variable is null')]
    public function testDefaultReturnsWestUrlWhenEnvVariableIsNull(): void
    {
        $_ENV[self::ENV_VAR_NAME] = null;

        $this->assertEquals(
            DefaultOAuthServerUrl::west(),
            DefaultOAuthServerUrl::default()
        );
    }

    #[Test]
    #[TestDox('tests east() returns non-empty string')]
    public function testEastReturnsNonEmptyString(): void
    {
        $this->assertNotEmpty(DefaultOAuthServerUrl::east());
        $this->assertIsString(DefaultOAuthServerUrl::east());
    }

    #[Test]
    #[TestDox('tests west() returns non-empty string')]
    public function testWestReturnsNonEmptyString(): void
    {
        $this->assertNotEmpty(DefaultOAuthServerUrl::west());
        $this->assertIsString(DefaultOAuthServerUrl::west());
    }

    #[Test]
    #[TestDox('tests default() returns non-empty string')]
    public function testDefaultReturnsNonEmptyString(): void
    {
        $this->assertNotEmpty(DefaultOAuthServerUrl::default());
        $this->assertIsString(DefaultOAuthServerUrl::default());
    }
}
