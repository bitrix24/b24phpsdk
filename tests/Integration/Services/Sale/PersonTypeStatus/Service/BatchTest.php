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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\PersonTypeStatus\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\Batch;
use Bitrix24\SDK\Services\Sale\PersonTypeStatus\Service\PersonTypeStatus;
use Bitrix24\SDK\Services\Sale\SaleServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Bitrix24\SDK\Tests\Integration\Services\Sale\SaleBatchFixturesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(\Bitrix24\SDK\Services\Sale\PersonTypeStatus\Batch::class)]
class BatchTest extends TestCase
{
    use SaleBatchFixturesTrait;

    private SaleServiceBuilder $saleServiceBuilder;

    private PersonTypeStatus $personTypeStatusService;

    /**
     * @var array<int, string> person type id => domain
     */
    private array $domains = [];

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->saleServiceBuilder = Fabric::getServiceBuilder()->getSaleScope();
        $this->personTypeStatusService = $this->saleServiceBuilder->personTypeStatus();
        $this->domains[$this->createPersonType()] = 'I';
        $this->domains[$this->createPersonType()] = 'E';
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->domains as $personTypeId => $domain) {
            $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->personTypeStatusService->delete($personTypeId, $domain));
            $this->cleanup(fn (): \Bitrix24\SDK\Core\Result\DeletedItemResult => $this->saleServiceBuilder->personType()->delete($personTypeId));
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('Batch::add, Batch::list person type statuses')]
    public function testAddList(): void
    {
        $items = [];
        foreach ($this->domains as $personTypeId => $domain) {
            $items[] = ['personTypeId' => $personTypeId, 'domain' => $domain];
        }

        $addedCount = 0;
        foreach ($this->personTypeStatusService->batch->add($items) as $addedItemResult) {
            $this->assertTrue($addedItemResult->isSuccess());
            $addedCount++;
        }

        $this->assertSame(count($this->domains), $addedCount);

        $listed = [];
        foreach ($this->personTypeStatusService->batch->list() as $item) {
            $listed[(int)$item->personTypeId] = (string)$item->domain;
        }

        foreach ($this->domains as $personTypeId => $domain) {
            $this->assertArrayHasKey($personTypeId, $listed);
            $this->assertSame($domain, $listed[$personTypeId]);
        }
    }
}
