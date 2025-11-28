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
        Uuid $applicationInstallationId,
        array $settingsData
    ): void {
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        foreach ($settingsData as $data) {
            $item = $this->createApplicationSettingsItemImplementation(
                Uuid::v7(),
                $applicationInstallationId,
                $data['key'],
                $data['value'],
                $data['userId'],
                $data['departmentId'],
                $data['isRequired']
            );
            $applicationSettingsItemRepository->save($item);
        }

        $testRepositoryFlusher->flush();

        $foundItems = $applicationSettingsItemRepository->findAllForInstallation($applicationInstallationId);
        $this->assertCount(count($settingsData), $foundItems);
    }

    #[Test]
    #[DataProvider('settingsByKeyDataProvider')]
    #[TestDox('test findAllForInstallationByKey method')]
    final public function testFindAllForInstallationByKey(
        Uuid $applicationInstallationId,
        string $key,
        array $settingsData
    ): void {
        $applicationSettingsItemRepository = $this->createApplicationSettingsRepositoryImplementation();
        $testRepositoryFlusher = $this->createRepositoryFlusherImplementation();

        foreach ($settingsData as $data) {
            $item = $this->createApplicationSettingsItemImplementation(
                Uuid::v7(),
                $applicationInstallationId,
                $data['key'],
                $data['value'],
                $data['userId'],
                $data['departmentId'],
                $data['isRequired']
            );
            $applicationSettingsItemRepository->save($item);
        }

        $testRepositoryFlusher->flush();

        $foundItems = $applicationSettingsItemRepository->findAllForInstallationByKey($applicationInstallationId, $key);
        $this->assertCount(count($settingsData), $foundItems);

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
        $applicationInstallationId = Uuid::v7();
        $settingsData = [
            ['key' => 'setting1', 'value' => 'value1', 'userId' => null, 'departmentId' => null, 'isRequired' => false],
            ['key' => 'setting2', 'value' => 'value2', 'userId' => null, 'departmentId' => null, 'isRequired' => false],
            ['key' => 'setting3', 'value' => 'value3', 'userId' => 123, 'departmentId' => null, 'isRequired' => false],
        ];

        yield 'multiple-settings' => [
            $applicationInstallationId,
            $settingsData
        ];
    }

    public static function settingsByKeyDataProvider(): Generator
    {
        $applicationInstallationId = Uuid::v7();
        $key = 'shared.setting';
        $settingsData = [
            ['key' => $key, 'value' => 'global-value', 'userId' => null, 'departmentId' => null, 'isRequired' => false],
            ['key' => $key, 'value' => 'user-value', 'userId' => 123, 'departmentId' => null, 'isRequired' => false],
            ['key' => $key, 'value' => 'dept-value', 'userId' => null, 'departmentId' => 456, 'isRequired' => false],
        ];

        yield 'settings-by-key' => [
            $applicationInstallationId,
            $key,
            $settingsData
        ];
    }
}
