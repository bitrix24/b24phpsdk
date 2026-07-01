<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Role\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\RoleItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Service\Role;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class RoleTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Role\Service
 */
#[CoversMethod(Role::class, 'add')]
#[CoversMethod(Role::class, 'delete')]
#[CoversMethod(Role::class, 'get')]
#[CoversMethod(Role::class, 'list')]
#[CoversMethod(Role::class, 'update')]
#[CoversMethod(Role::class, 'count')]
#[CoversMethod(Role::class, 'fillAccesses')]
#[\PHPUnit\Framework\Attributes\CoversClass(Role::class)]
class RoleTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Role $roleService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->roleService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->role();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: create a test role and return its id.
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function createRole(): int
    {
        return $this->roleService->add([
            'name' => 'SDK_TEST_' . $this->faker->uuid(),
            'code' => 'SDK_TEST_' . strtoupper(substr(str_replace('-', '_', $this->faker->uuid()), 0, 20)),
        ])->getId();
    }

    /**
     * Helper: silently delete a role.
     */
    private function safeDelete(int $id): void
    {
        try {
            $this->roleService->delete($id);
        } catch (BaseException) {
            // Server-side error; ignored during cleanup
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $id = $this->createRole();
        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $id = $this->createRole();

        $roleItemResult = $this->roleService->get($id)->role();
        self::assertInstanceOf(RoleItemResult::class, $roleItemResult);
        self::assertEquals($id, $roleItemResult->id);

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $id = $this->createRole();

        $list = $this->roleService->list()->getRoles();
        self::assertIsArray($list);
        self::assertGreaterThanOrEqual(1, count($list));

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $id = $this->createRole();

        $updatedName = 'SDK_TEST_UPDATED_' . $this->faker->uuid();
        self::assertTrue(
            $this->roleService->update($id, ['name' => $updatedName])->isSuccess()
        );

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $id = $this->createRole();

        $deletedRoleResult = $this->roleService->delete($id);
        self::assertTrue($deletedRoleResult->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $countBefore = $this->roleService->count();

        $id = $this->createRole();

        $countAfter = $this->roleService->count();
        self::assertEquals($countBefore + 1, $countAfter);

        // Cleanup
        $this->safeDelete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFillAccesses(): void
    {
        $id = $this->createRole();

        $fillAccessesResult = $this->roleService->fillAccesses([
            [
                'roleId' => $id,
                'accessCode' => 'UA',
            ],
        ]);
        self::assertTrue($fillAccessesResult->isSuccess());

        // Cleanup
        $this->safeDelete($id);
    }
}
