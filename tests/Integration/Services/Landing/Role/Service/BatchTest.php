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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Role\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Role\Result\RoleItemResult;
use Bitrix24\SDK\Services\Landing\Role\Service\Batch;
use Bitrix24\SDK\Services\Landing\Role\Service\Role;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Role $roleService;

    #[\Override]
    protected function setUp(): void
    {
        $this->roleService = Factory::getServiceBuilder(true)->getLandingScope()->role();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns roles for sites and stores by default')]
    public function testBatchListDefault(): void
    {
        $expectedIds = array_map(
            static fn(RoleItemResult $roleItemResult): int => (int)$roleItemResult->ID,
            $this->roleService->getList()->getRoles()
        );

        $foundIds = [];
        foreach ($this->roleService->batch->list() as $roleItemResult) {
            self::assertInstanceOf(RoleItemResult::class, $roleItemResult);
            $foundIds[] = (int)$roleItemResult->ID;
        }

        self::assertSame($expectedIds, $foundIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns roles for several site types')]
    public function testBatchListForSeveralScopes(): void
    {
        $defaultCount = count(iterator_to_array($this->roleService->batch->list(), false));
        $knowledgeCount = count(iterator_to_array($this->roleService->batch->list(['KNOWLEDGE']), false));

        $foundIds = [];
        foreach ($this->roleService->batch->list(['', 'KNOWLEDGE']) as $roleItemResult) {
            $foundIds[] = (int)$roleItemResult->ID;
        }

        self::assertCount($defaultCount + $knowledgeCount, $foundIds);
        self::assertSame($foundIds, array_values(array_unique($foundIds)));
    }
}
