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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Catalog\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Catalog\Result\CatalogItemResult;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Catalog $catalogService;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogService = Factory::getServiceBuilder()->getCatalogScope()->catalog();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $expectedIds = [];
        foreach ($this->catalogService->list(['id'], [], ['id' => 'ASC'], 0)->getCatalogs() as $catalog) {
            $expectedIds[] = $catalog->id;
        }

        self::assertNotEmpty($expectedIds, 'portal has no commercial catalogs');

        $actualIds = [];
        foreach ($this->catalogService->batch->list(['id' => 'ASC'], [], ['id']) as $item) {
            self::assertInstanceOf(CatalogItemResult::class, $item);
            $actualIds[] = $item->id;
        }

        foreach ($expectedIds as $expectedId) {
            self::assertContains($expectedId, $actualIds);
        }
    }
}
