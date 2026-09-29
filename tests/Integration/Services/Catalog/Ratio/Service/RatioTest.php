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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Ratio\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatioItemResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Service\Ratio;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Ratio::class, 'get')]
#[CoversMethod(Ratio::class, 'list')]
#[CoversMethod(Ratio::class, 'fields')]
class RatioTest extends TestCase
{
    private Ratio $ratioService;

    #[\Override]
    protected function setUp(): void
    {
        $this->ratioService = Fabric::getServiceBuilder()->getCatalogScope()->ratio();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $fields = $this->ratioService->fields()->getFieldsDescription();

        self::assertArrayHasKey('ratio', $fields);
        self::assertArrayHasKey('id', $fields['ratio']);
        self::assertArrayHasKey('isDefault', $fields['ratio']);
        self::assertArrayHasKey('productId', $fields['ratio']);
        self::assertArrayHasKey('ratio', $fields['ratio']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $ratiosResult = $this->ratioService->list();

        self::assertIsArray($ratiosResult->getRatios());
        self::assertGreaterThanOrEqual(0, $ratiosResult->getTotal());
    }

    /**
     * catalog.ratio has no REST method to create a ratio — ratios are created implicitly when a
     * product's measurement unit ratio is configured.
     * If the portal has none, this test is skipped as there is no way to fabricate one via REST.
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $ratios = $this->ratioService->list()->getRatios();
        if ($ratios === []) {
            $this->markTestSkipped('portal has no catalog ratios (catalog.ratio) configured to test get() against');
        }

        $firstRatio = $ratios[0];
        $ratioItemResult = $this->ratioService->get($firstRatio->id)->ratio();

        self::assertInstanceOf(RatioItemResult::class, $ratioItemResult);
        self::assertEquals($firstRatio->id, $ratioItemResult->id);
    }
}
