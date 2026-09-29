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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Extra\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Extra\Result\ExtraItemResult;
use Bitrix24\SDK\Services\Catalog\Extra\Service\Batch;
use Bitrix24\SDK\Services\Catalog\Extra\Service\Extra;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Extra $extraService;

    #[\Override]
    protected function setUp(): void
    {
        $this->extraService = Factory::getServiceBuilder()->getCatalogScope()->extra();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testBatchList(): void
    {
        $expectedIds = [];
        foreach ($this->extraService->list(['id'])->getExtras() as $extra) {
            $expectedIds[] = $extra->id;
        }

        $actualIds = [];
        foreach ($this->extraService->batch->list(['id' => 'ASC'], [], ['id']) as $item) {
            self::assertInstanceOf(ExtraItemResult::class, $item);
            $actualIds[] = $item->id;
        }

        self::assertCount(count($expectedIds), $actualIds);
        foreach ($expectedIds as $expectedId) {
            self::assertContains($expectedId, $actualIds);
        }
    }
}
