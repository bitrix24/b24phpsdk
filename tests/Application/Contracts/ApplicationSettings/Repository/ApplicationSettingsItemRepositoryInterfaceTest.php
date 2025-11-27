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

namespace Bitrix24\SDK\Tests\Application\Contracts\ApplicationSettings\Repository;

use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity\ApplicationSettingsItemInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Exceptions\ApplicationSettingsItemNotFoundException;
use Bitrix24\SDK\Application\Contracts\ApplicationSettings\Repository\ApplicationSettingsItemRepositoryInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Tests\Application\Contracts\TestRepositoryFlusherInterface;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationSettingsItemRepositoryInterface::class)]
abstract class ApplicationSettingsItemRepositoryInterfaceTest extends TestCase
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

    abstract protected function createApplicationSettingsItemRepositoryImplementation(): ApplicationSettingsItemRepositoryInterface;

    abstract protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface;

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test save method')]
    final public function testSave(
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

        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $repository->save($item);
        $flusher->flush();

        $savedItem = $repository->getById($uuid);
        $this->assertEquals($item->getId(), $savedItem->getId());
        $this->assertEquals($item->getKey(), $savedItem->getKey());
        $this->assertEquals($item->getValue(), $savedItem->getValue());
    }

    #[Test]
    #[TestDox('test getById method with non-existing id')]
    final public function testGetByIdNotExists(): void
    {
        $this->expectException(ApplicationSettingsItemNotFoundException::class);
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $repository->getById(Uuid::v7());
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test findById method with existing id')]
    final public function testFindByIdExists(
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

        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $repository->save($item);
        $flusher->flush();

        $foundItem = $repository->findById($uuid);
        $this->assertNotNull($foundItem);
        $this->assertEquals($item->getId(), $foundItem->getId());
    }

    #[Test]
    #[TestDox('test findById method with non-existing id')]
    final public function testFindByIdNotExists(): void
    {
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $foundItem = $repository->findById(Uuid::v7());
        $this->assertNull($foundItem);
    }

    #[Test]
    #[DataProvider('applicationSettingsItemDataProvider')]
    #[TestDox('test delete method')]
    final public function testDelete(
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

        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $repository->save($item);
        $flusher->flush();

        $item->markAsDeleted();
        $repository->save($item);
        $flusher->flush();

        $repository->delete($uuid);
        $flusher->flush();

        $this->expectException(ApplicationSettingsItemNotFoundException::class);
        $repository->getById($uuid);
    }

    #[Test]
    #[TestDox('test delete method with non-existing id')]
    final public function testDeleteNotExists(): void
    {
        $this->expectException(ApplicationSettingsItemNotFoundException::class);
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $repository->delete(Uuid::v7());
    }

    #[Test]
    #[DataProvider('multipleSettingsDataProvider')]
    #[TestDox('test findAllByApplicationInstallationId method')]
    final public function testFindAllByApplicationInstallationId(
        Uuid $applicationInstallationId,
        array $items
    ): void {
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        foreach ($items as $item) {
            $repository->save($item);
        }
        $flusher->flush();

        $foundItems = $repository->findAllByApplicationInstallationId($applicationInstallationId);
        $this->assertCount(count($items), $foundItems);
    }

    #[Test]
    #[DataProvider('settingsByKeyDataProvider')]
    #[TestDox('test findByApplicationInstallationIdAndKey method')]
    final public function testFindByApplicationInstallationIdAndKey(
        Uuid $applicationInstallationId,
        string $key,
        array $items
    ): void {
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        foreach ($items as $item) {
            $repository->save($item);
        }
        $flusher->flush();

        $foundItems = $repository->findByApplicationInstallationIdAndKey($applicationInstallationId, $key);
        $this->assertCount(count($items), $foundItems);

        foreach ($foundItems as $foundItem) {
            $this->assertEquals($key, $foundItem->getKey());
        }
    }

    #[Test]
    #[DataProvider('personalSettingDataProvider')]
    #[TestDox('test findByApplicationInstallationIdAndKeyAndUserId method')]
    final public function testFindByApplicationInstallationIdAndKeyAndUserId(
        Uuid $applicationInstallationId,
        string $key,
        int $userId,
        ApplicationSettingsItemInterface $item
    ): void {
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $repository->save($item);
        $flusher->flush();

        $foundItem = $repository->findByApplicationInstallationIdAndKeyAndUserId(
            $applicationInstallationId,
            $key,
            $userId
        );

        $this->assertNotNull($foundItem);
        $this->assertEquals($item->getId(), $foundItem->getId());
        $this->assertEquals($userId, $foundItem->getBitrix24UserId());
    }

    #[Test]
    #[DataProvider('departmentalSettingDataProvider')]
    #[TestDox('test findByApplicationInstallationIdAndKeyAndDepartmentId method')]
    final public function testFindByApplicationInstallationIdAndKeyAndDepartmentId(
        Uuid $applicationInstallationId,
        string $key,
        int $departmentId,
        ApplicationSettingsItemInterface $item
    ): void {
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $repository->save($item);
        $flusher->flush();

        $foundItem = $repository->findByApplicationInstallationIdAndKeyAndDepartmentId(
            $applicationInstallationId,
            $key,
            $departmentId
        );

        $this->assertNotNull($foundItem);
        $this->assertEquals($item->getId(), $foundItem->getId());
        $this->assertEquals($departmentId, $foundItem->getBitrix24DepartmentId());
    }

    #[Test]
    #[DataProvider('globalSettingDataProvider')]
    #[TestDox('test findGlobalByApplicationInstallationIdAndKey method')]
    final public function testFindGlobalByApplicationInstallationIdAndKey(
        Uuid $applicationInstallationId,
        string $key,
        ApplicationSettingsItemInterface $item
    ): void {
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        $flusher = $this->createRepositoryFlusherImplementation();

        $repository->save($item);
        $flusher->flush();

        $foundItem = $repository->findGlobalByApplicationInstallationIdAndKey(
            $applicationInstallationId,
            $key
        );

        $this->assertNotNull($foundItem);
        $this->assertEquals($item->getId(), $foundItem->getId());
        $this->assertTrue($foundItem->isGlobal());
    }

    #[Test]
    #[TestDox('test findByApplicationInstallationIdAndKey with empty key')]
    final public function testFindByApplicationInstallationIdAndKeyWithEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $repository = $this->createApplicationSettingsItemRepositoryImplementation();
        /** @phpstan-ignore-next-line */
        $repository->findByApplicationInstallationIdAndKey(Uuid::v7(), '');
    }

    public static function applicationSettingsItemDataProvider(): Generator
    {
        yield 'global-setting' => [
            Uuid::v7(),
            Uuid::v7(),
            'theme.color',
            'blue',
            null,
            null,
            true
        ];
    }

    public static function multipleSettingsDataProvider(): Generator
    {
        $applicationInstallationId = Uuid::v7();
        $items = [];

        $items[] = self::createTestItem($applicationInstallationId, 'setting1', 'value1', null, null);
        $items[] = self::createTestItem($applicationInstallationId, 'setting2', 'value2', null, null);
        $items[] = self::createTestItem($applicationInstallationId, 'setting3', 'value3', 123, null);

        yield 'multiple-settings' => [
            $applicationInstallationId,
            $items
        ];
    }

    public static function settingsByKeyDataProvider(): Generator
    {
        $applicationInstallationId = Uuid::v7();
        $key = 'shared.setting';
        $items = [];

        $items[] = self::createTestItem($applicationInstallationId, $key, 'global-value', null, null);
        $items[] = self::createTestItem($applicationInstallationId, $key, 'user-value', 123, null);
        $items[] = self::createTestItem($applicationInstallationId, $key, 'dept-value', null, 456);

        yield 'settings-by-key' => [
            $applicationInstallationId,
            $key,
            $items
        ];
    }

    public static function personalSettingDataProvider(): Generator
    {
        $applicationInstallationId = Uuid::v7();
        $key = 'user.preference';
        $userId = 123;

        yield 'personal-setting' => [
            $applicationInstallationId,
            $key,
            $userId,
            self::createTestItem($applicationInstallationId, $key, 'user-value', $userId, null)
        ];
    }

    public static function departmentalSettingDataProvider(): Generator
    {
        $applicationInstallationId = Uuid::v7();
        $key = 'department.config';
        $departmentId = 456;

        yield 'departmental-setting' => [
            $applicationInstallationId,
            $key,
            $departmentId,
            self::createTestItem($applicationInstallationId, $key, 'dept-value', null, $departmentId)
        ];
    }

    public static function globalSettingDataProvider(): Generator
    {
        $applicationInstallationId = Uuid::v7();
        $key = 'global.config';

        yield 'global-setting' => [
            $applicationInstallationId,
            $key,
            self::createTestItem($applicationInstallationId, $key, 'global-value', null, null)
        ];
    }

    private static function createTestItem(
        Uuid $applicationInstallationId,
        string $key,
        string $value,
        ?int $userId,
        ?int $departmentId
    ): ApplicationSettingsItemInterface {
        // This is a placeholder - will be overridden by concrete test implementations
        // We need to use a mock or stub here since we can't instantiate the interface
        return new class($applicationInstallationId, $key, $value, $userId, $departmentId) implements ApplicationSettingsItemInterface {
            private Uuid $id;
            private string $status = 'active';
            private ?int $changedBy = null;

            public function __construct(
                private Uuid $appInstallId,
                private string $key,
                private string $value,
                private ?int $userId,
                private ?int $deptId
            ) {
                $this->id = Uuid::v7();
            }

            public function getId(): Uuid { return $this->id; }
            public function getApplicationInstallationId(): Uuid { return $this->appInstallId; }
            public function getKey(): string { return $this->key; }
            public function getValue(): string { return $this->value; }
            public function getBitrix24UserId(): ?int { return $this->userId; }
            public function getBitrix24DepartmentId(): ?int { return $this->deptId; }
            public function getChangedByBitrix24UserId(): ?int { return $this->changedBy; }
            public function isRequired(): bool { return false; }
            public function isActive(): bool { return $this->status === 'active'; }
            public function getStatus(): \Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity\ApplicationSettingStatus {
                return \Bitrix24\SDK\Application\Contracts\ApplicationSettings\Entity\ApplicationSettingStatus::active;
            }
            public function getCreatedAt(): \Carbon\CarbonImmutable { return \Carbon\CarbonImmutable::now(); }
            public function getUpdatedAt(): \Carbon\CarbonImmutable { return \Carbon\CarbonImmutable::now(); }
            public function updateValue(string $value, ?int $changedByBitrix24UserId = null): void {
                $this->value = $value;
                $this->changedBy = $changedByBitrix24UserId;
            }
            public function markAsDeleted(): void { $this->status = 'deleted'; }
            public function isGlobal(): bool { return $this->userId === null && $this->deptId === null; }
            public function isPersonal(): bool { return $this->userId !== null; }
            public function isDepartmental(): bool { return $this->deptId !== null; }
        };
    }
}
