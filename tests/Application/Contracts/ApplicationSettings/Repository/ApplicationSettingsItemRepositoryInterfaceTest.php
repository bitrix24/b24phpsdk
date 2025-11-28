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

    abstract protected function createRepositoryFlusherImplementation(): TestRepositoryFlusherInterface;

    abstract protected function createApplicationSettingsRepositoryImplementation(): ApplicationSettingsItemRepositoryInterface;

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
        $applicationSettingsItem = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        $applicationSettingsItemRepository->save($applicationSettingsItem);
        $testRepositoryFlusher->flush();

        $savedItem = $applicationSettingsItemRepository->getById($uuid);
        $this->assertEquals($applicationSettingsItem->getId(), $savedItem->getId());
        $this->assertEquals($applicationSettingsItem->getKey(), $savedItem->getKey());
        $this->assertEquals($applicationSettingsItem->getValue(), $savedItem->getValue());
    }

    #[Test]
    #[TestDox('test getById method with non-existing id')]
    final public function testGetByIdNotExists(): void
    {
        $this->expectException(ApplicationSettingsItemNotFoundException::class);
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $applicationSettingsItemRepository->getById(Uuid::v7());
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
        $applicationSettingsItem = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        $applicationSettingsItemRepository->save($applicationSettingsItem);
        $testRepositoryFlusher->flush();

        $foundItem = $applicationSettingsItemRepository->findById($uuid);
        $this->assertNotNull($foundItem);
        $this->assertEquals($applicationSettingsItem->getId(), $foundItem->getId());
    }

    #[Test]
    #[TestDox('test findById method with non-existing id')]
    final public function testFindByIdNotExists(): void
    {
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $foundItem = $applicationSettingsItemRepository->findById(Uuid::v7());
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
        $applicationSettingsItem = $this->createApplicationSettingsItemImplementation(
            $uuid,
            $applicationInstallationId,
            $key,
            $value,
            $bitrix24UserId,
            $bitrix24DepartmentId,
            $isRequired
        );

        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        $applicationSettingsItemRepository->save($applicationSettingsItem);
        $testRepositoryFlusher->flush();

        $applicationSettingsItem->markAsDeleted();
        $applicationSettingsItemRepository->save($applicationSettingsItem);
        $testRepositoryFlusher->flush();

        $applicationSettingsItemRepository->delete($uuid);
        $testRepositoryFlusher->flush();

        $this->expectException(ApplicationSettingsItemNotFoundException::class);
        $applicationSettingsItemRepository->getById($uuid);
    }

    #[Test]
    #[TestDox('test delete method with non-existing id')]
    final public function testDeleteNotExists(): void
    {
        $this->expectException(ApplicationSettingsItemNotFoundException::class);
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $applicationSettingsItemRepository->delete(Uuid::v7());
    }

    #[Test]
    #[DataProvider('multipleSettingsDataProvider')]
    #[TestDox('test findAllForInstallation method')]
    final public function testFindAllForInstallation(
        Uuid $uuid,
        array $items
    ): void {
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        foreach ($items as $item) {
            $applicationSettingsItemRepository->save($item);
        }

        $testRepositoryFlusher->flush();

        $foundItems = $applicationSettingsItemRepository->findAllForInstallation($uuid);
        $this->assertCount(count($items), $foundItems);
    }

    #[Test]
    #[DataProvider('settingsByKeyDataProvider')]
    #[TestDox('test findAllForInstallationByKey method')]
    final public function testFindAllForInstallationByKey(
        Uuid $uuid,
        string $key,
        array $items
    ): void {
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        foreach ($items as $item) {
            $applicationSettingsItemRepository->save($item);
        }

        $testRepositoryFlusher->flush();

        $foundItems = $applicationSettingsItemRepository->findAllForInstallationByKey($uuid, $key);
        $this->assertCount(count($items), $foundItems);

        foreach ($foundItems as $foundItem) {
            $this->assertEquals($key, $foundItem->getKey());
        }
    }

    #[Test]
    #[TestDox('test findAllForInstallationByKey with empty key')]
    final public function testFindAllForInstallationByKeyWithEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        /** @phpstan-ignore-next-line */
        $applicationSettingsItemRepository->findAllForInstallationByKey(Uuid::v7(), '');
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
        $uuidV7 = Uuid::v7();
        $items = [];

        $items[] = self::createTestItem($uuidV7, 'setting1', 'value1', null, null);
        $items[] = self::createTestItem($uuidV7, 'setting2', 'value2', null, null);
        $items[] = self::createTestItem($uuidV7, 'setting3', 'value3', 123, null);

        yield 'multiple-settings' => [
            $uuidV7,
            $items
        ];
    }

    public static function settingsByKeyDataProvider(): Generator
    {
        $uuidV7 = Uuid::v7();
        $key = 'shared.setting';
        $items = [];

        $items[] = self::createTestItem($uuidV7, $key, 'global-value', null, null);
        $items[] = self::createTestItem($uuidV7, $key, 'user-value', 123, null);
        $items[] = self::createTestItem($uuidV7, $key, 'dept-value', null, 456);

        yield 'settings-by-key' => [
            $uuidV7,
            $key,
            $items
        ];
    }

    private static function createTestItem(
        Uuid $uuid,
        string $key,
        string $value,
        ?int $userId,
        ?int $departmentId
    ): ApplicationSettingsItemInterface {
        // This is a placeholder - will be overridden by concrete test implementations
        // We need to use a mock or stub here since we can't instantiate the interface
        return new class($uuid, $key, $value, $userId, $departmentId) implements ApplicationSettingsItemInterface {
            private readonly Uuid $id;

            private string $status = 'active';

            private ?int $changedBy = null;

            public function __construct(
                private readonly Uuid $appInstallId,
                private readonly string $key,
                private string $value,
                private readonly ?int $userId,
                private readonly ?int $deptId
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
