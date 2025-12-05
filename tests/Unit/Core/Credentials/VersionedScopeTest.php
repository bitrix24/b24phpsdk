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

use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Credentials\VersionedScope;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(VersionedScope::class)]
class VersionedScopeTest extends TestCase
{
    #[Test]
    #[TestDox('Test construction with single version')]
    public function testConstructWithSingleVersion(): void
    {
        $scope = new Scope(['crm', 'telephony']);
        $versionedScope = new VersionedScope([1 => $scope]);

        $this->assertInstanceOf(VersionedScope::class, $versionedScope);
        $this->assertEquals([1], $versionedScope->getVersions());
    }

    #[Test]
    #[TestDox('Test construction with multiple versions')]
    public function testConstructWithMultipleVersions(): void
    {
        $scope1 = new Scope(['crm']);
        $scope2 = new Scope(['telephony']);
        $scope3 = new Scope(['user', 'im']);

        $versionedScope = new VersionedScope([
            1 => $scope1,
            2 => $scope2,
            5 => $scope3,
        ]);

        $this->assertEquals([1, 2, 5], $versionedScope->getVersions());
    }

    #[Test]
    #[TestDox('Test construction with non-sequential versions')]
    public function testConstructWithNonSequentialVersions(): void
    {
        $scope1 = new Scope(['crm']);
        $scope2 = new Scope(['telephony']);
        $scope3 = new Scope(['user']);
        $scope4 = new Scope(['im']);

        $versionedScope = new VersionedScope([
            1 => $scope1,
            10 => $scope2,
            5 => $scope3,
            100 => $scope4,
        ]);

        $this->assertEquals([1, 5, 10, 100], $versionedScope->getVersions());
    }

    #[Test]
    #[DataProvider('invalidVersionProvider')]
    #[TestDox('Test construction with invalid version throws exception')]
    public function testConstructWithInvalidVersion(array $scopes, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new VersionedScope($scopes);
    }

    public static function invalidVersionProvider(): Generator
    {
        yield 'version zero' => [
            [0 => new Scope(['crm'])],
            'Version must be >= 1, got 0',
        ];

        yield 'negative version' => [
            [-1 => new Scope(['crm'])],
            'Version must be >= 1, got -1',
        ];

        yield 'large negative version' => [
            [-100 => new Scope(['crm'])],
            'Version must be >= 1, got -100',
        ];

        yield 'string version key' => [
            ['version1' => new Scope(['crm'])],
            'Version must be an integer, got string',
        ];

        yield 'invalid scope value' => [
            [1 => 'not a scope'],
            'Value must be instance of Scope, got string',
        ];

        yield 'invalid scope object' => [
            [1 => new stdClass()],
            'Value must be instance of Scope, got stdClass',
        ];
    }

    #[Test]
    #[TestDox('Test construction with empty array throws exception')]
    public function testConstructWithEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one scope version must be provided');

        new VersionedScope([]);
    }

    #[Test]
    #[TestDox('Test construction with mixed valid and invalid versions')]
    public function testConstructWithMixedValidAndInvalidVersions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be >= 1, got 0');

        new VersionedScope([
            1 => new Scope(['crm']),
            0 => new Scope(['telephony']),
        ]);
    }

    #[Test]
    #[TestDox('Test getScope returns correct Scope instance')]
    public function testGetScopeReturnsCorrectInstance(): void
    {
        $scope1 = new Scope(['crm']);
        $scope2 = new Scope(['telephony']);

        $versionedScope = new VersionedScope([
            1 => $scope1,
            2 => $scope2,
        ]);

        $this->assertSame($scope1, $versionedScope->getScope(1));
        $this->assertSame($scope2, $versionedScope->getScope(2));
    }

    #[Test]
    #[TestDox('Test getScope with non-existent version throws exception')]
    public function testGetScopeWithNonExistentVersion(): void
    {
        $scope = new Scope(['crm']);
        $versionedScope = new VersionedScope([1 => $scope]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version 2 does not exist');

        $versionedScope->getScope(2);
    }

    #[Test]
    #[TestDox('Test getScope from container with multiple versions')]
    public function testGetScopeFromMultipleVersions(): void
    {
        $scope1 = new Scope(['crm']);
        $scope5 = new Scope(['telephony']);
        $scope10 = new Scope(['user', 'im']);

        $versionedScope = new VersionedScope([
            1 => $scope1,
            5 => $scope5,
            10 => $scope10,
        ]);

        $this->assertSame($scope1, $versionedScope->getScope(1));
        $this->assertSame($scope5, $versionedScope->getScope(5));
        $this->assertSame($scope10, $versionedScope->getScope(10));
    }

    #[Test]
    #[DataProvider('getVersionsProvider')]
    #[TestDox('Test getVersions returns sorted array')]
    public function testGetVersionsReturnsSortedArray(array $scopes, array $expectedVersions): void
    {
        $versionedScope = new VersionedScope($scopes);
        $this->assertEquals($expectedVersions, $versionedScope->getVersions());
    }

    public static function getVersionsProvider(): Generator
    {
        yield 'single version' => [
            [1 => new Scope(['crm'])],
            [1],
        ];

        yield 'multiple sequential versions' => [
            [
                1 => new Scope(['crm']),
                2 => new Scope(['telephony']),
                5 => new Scope(['user']),
            ],
            [1, 2, 5],
        ];

        yield 'non-sequential versions sorted' => [
            [
                10 => new Scope(['crm']),
                1 => new Scope(['telephony']),
                100 => new Scope(['user']),
                5 => new Scope(['im']),
            ],
            [1, 5, 10, 100],
        ];
    }

    #[Test]
    #[TestDox('Test hasVersion returns true for existing version')]
    public function testHasVersionReturnsTrueForExistingVersion(): void
    {
        $versionedScope = new VersionedScope([
            1 => new Scope(['crm']),
            5 => new Scope(['telephony']),
        ]);

        $this->assertTrue($versionedScope->hasVersion(1));
        $this->assertTrue($versionedScope->hasVersion(5));
    }

    #[Test]
    #[TestDox('Test hasVersion returns false for non-existent version')]
    public function testHasVersionReturnsFalseForNonExistentVersion(): void
    {
        $versionedScope = new VersionedScope([
            1 => new Scope(['crm']),
        ]);

        $this->assertFalse($versionedScope->hasVersion(2));
        $this->assertFalse($versionedScope->hasVersion(10));
        $this->assertFalse($versionedScope->hasVersion(0));
    }

    #[Test]
    #[TestDox('Test hasVersion works with multiple versions')]
    public function testHasVersionWorksWithMultipleVersions(): void
    {
        $versionedScope = new VersionedScope([
            1 => new Scope(['crm']),
            5 => new Scope(['telephony']),
            10 => new Scope(['user']),
            100 => new Scope(['im']),
        ]);

        $this->assertTrue($versionedScope->hasVersion(1));
        $this->assertFalse($versionedScope->hasVersion(2));
        $this->assertFalse($versionedScope->hasVersion(3));
        $this->assertFalse($versionedScope->hasVersion(4));
        $this->assertTrue($versionedScope->hasVersion(5));
        $this->assertFalse($versionedScope->hasVersion(6));
        $this->assertTrue($versionedScope->hasVersion(10));
        $this->assertTrue($versionedScope->hasVersion(100));
        $this->assertFalse($versionedScope->hasVersion(101));
    }
}
