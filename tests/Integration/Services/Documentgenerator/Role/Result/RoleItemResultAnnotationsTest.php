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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Role\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Role\Result\RoleItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Role\Service\Role;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoleItemResult::class)]
class RoleItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Role $roleService;

    private Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->roleService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->role();
        $this->faker = FakerFactory::create();
    }

    /**
     * Helper: create a role, fetch it via get() to obtain the full field set, then delete it.
     *
     * NOTE: documentgenerator.role.list() returns a reduced set of fields (id, name, code)
     * without the permissions field. documentgenerator.role.get() returns the full set including permissions.
     * We therefore validate annotations against the get() response.
     *
     * @return array<string, mixed>
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstRoleRawItem(): array
    {
        $id = $this->roleService->add([
            'name' => 'SDK_ANNOT_TEST_' . $this->faker->uuid(),
        ])->getId();

        $rawItem = $this->roleService->get($id)
            ->getCoreResponse()->getResponseData()->getResult()['role'] ?? [];

        try {
            $this->roleService->delete($id);
        } catch (BaseException) {
            // Server-side error during cleanup; must not affect annotations test
        }

        self::assertNotEmpty($rawItem, 'get() must return a role item to run this test');

        return $rawItem;
    }

    #[Test]
    #[TestDox('all fields in RoleItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstRoleRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            RoleItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in RoleItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstRoleRawItem();
        $roleItemResult = new RoleItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $roleItemResult,
            RoleItemResult::class
        );
    }
}
