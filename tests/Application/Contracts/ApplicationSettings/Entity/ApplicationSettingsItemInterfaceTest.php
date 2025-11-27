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

namespace Bitrix24\SDK\Tests\Application\Contracts\ApplicationSettings\Entity;

use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity\ApplicationSettingsItemInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity\ApplicationSettingStatus;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationSettingsItemInterface::class)]
abstract class ApplicationSettingsItemInterfaceTest extends TestCase
{
    abstract protected function createApplicationSettingsItemImplementation(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): ApplicationSettingsItemInterface;

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getId method')]
    final public function testGetId(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($uuid, $item->getId());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getApplicationInstallationId method')]
    final public function testGetApplicationInstallationId(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($applicationInstallationId, $item->getApplicationInstallationId());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getKey method')]
    final public function testGetKey(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($key, $item->getKey());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getValue method')]
    final public function testGetValue(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($value, $item->getValue());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getBitrix24UserId method')]
    final public function testGetBitrix24UserId(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($bitrix24UserId, $item->getBitrix24UserId());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getBitrix24DepartmentId method')]
    final public function testGetBitrix24DepartmentId(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($bitrix24DepartmentId, $item->getBitrix24DepartmentId());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test isRequired method')]
    final public function testIsRequired(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals($isRequired, $item->isRequired());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getStatus method')]
    final public function testGetStatus(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );
        $this->assertEquals(ApplicationSettingStatus::active, $item->getStatus());
        $this->assertTrue($item->isActive());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test updateValue method')]
    final public function testUpdateValue(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $newValue = 'new-value';
        $changedBy = 123;
        $item->updateValue($newValue, $changedBy);

        $this->assertEquals($newValue, $item->getValue());
        $this->assertEquals($changedBy, $item->getChangedByBitrix24UserId());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test markAsDeleted method')]
    final public function testMarkAsDeleted(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $this->assertTrue($item->isActive());
        $item->markAsDeleted();
        $this->assertFalse($item->isActive());
        $this->assertEquals(ApplicationSettingStatus::deleted, $item->getStatus());
    }

    #[Test]
    #[DataProvider('globalSettingsDataProvider')]
    #[TestDox('test isGlobal method')]
    final public function testIsGlobal(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $this->assertTrue($item->isGlobal());
        $this->assertFalse($item->isPersonal());
        $this->assertFalse($item->isDepartmental());
    }

    #[Test]
    #[DataProvider('personalSettingsDataProvider')]
    #[TestDox('test isPersonal method')]
    final public function testIsPersonal(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $this->assertFalse($item->isGlobal());
        $this->assertTrue($item->isPersonal());
        $this->assertFalse($item->isDepartmental());
    }

    #[Test]
    #[DataProvider('departmentalSettingsDataProvider')]
    #[TestDox('test isDepartmental method')]
    final public function testIsDepartmental(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $this->assertFalse($item->isGlobal());
        $this->assertFalse($item->isPersonal());
        $this->assertTrue($item->isDepartmental());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test getCreatedAt and getUpdatedAt methods')]
    final public function testTimestamps(
        Uuid $uuid,
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $bitrix24UserId,
        ?int $bitrix24DepartmentId,
        bool $isRequired
    ): void {
        $item = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $this->assertNotNull($item->getCreatedAt());
        $this->assertNotNull($item->getUpdatedAt());
    }

    public static function applicationSettingsItemDataProvider(): Generator
    {
        yield 'global-required' => [
            Uuid::v7(),
            Uuid::v7(),
            'theme.color',
            'blue',
            null,
            null,
            true
        ];

        yield 'global-optional' => [
            Uuid::v7(),
            Uuid::v7(),
            'notification.enabled',
            'true',
            null,
            null,
            false
        ];

        yield 'personal-required' => [
            Uuid::v7(),
            Uuid::v7(),
            'user.language',
            'en',
            123,
            null,
            true
        ];

        yield 'departmental-optional' => [
            Uuid::v7(),
            Uuid::v7(),
            'department.timezone',
            'UTC',
            null,
            456,
            false
        ];
    }

    public static function globalSettingsDataProvider(): Generator
    {
        yield 'global-setting' => [
            Uuid::v7(),
            Uuid::v7(),
            'global.setting',
            'value',
            null,
            null,
            false
        ];
    }

    public static function personalSettingsDataProvider(): Generator
    {
        yield 'personal-setting' => [
            Uuid::v7(),
            Uuid::v7(),
            'personal.setting',
            'value',
            123,
            null,
            false
        ];
    }

    public static function departmentalSettingsDataProvider(): Generator
    {
        yield 'departmental-setting' => [
            Uuid::v7(),
            Uuid::v7(),
            'departmental.setting',
            'value',
            null,
            456,
            false
        ];
    }
}
