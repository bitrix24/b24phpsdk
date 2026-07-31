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
use Bitrix24\SDK\Services\Catalog\Extra\Service\Extra;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Extra::class, 'get')]
#[CoversMethod(Extra::class, 'list')]
#[CoversMethod(Extra::class, 'fields')]
class ExtraTest extends TestCase
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
    public function testGetFields(): void
    {
        $fields = $this->extraService->fields()->getFieldsDescription();

        self::assertArrayHasKey('extra', $fields);
        self::assertArrayHasKey('id', $fields['extra']);
        self::assertArrayHasKey('name', $fields['extra']);
        self::assertArrayHasKey('percentage', $fields['extra']);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $extrasResult = $this->extraService->list();

        self::assertIsArray($extrasResult->getExtras());
        self::assertGreaterThanOrEqual(0, $extrasResult->getTotal());
    }

    /**
     * catalog.extra has no REST method to create a markup — markups are portal-configured.
     * If the portal has none, this test is skipped as there is no way to fabricate one via REST.
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $extras = $this->extraService->list()->getExtras();
        if ($extras === []) {
            $this->markTestSkipped('portal has no markups (catalog.extra) configured to test get() against');
        }

        $firstExtra = $extras[0];
        $extraItemResult = $this->extraService->get($firstExtra->id)->extra();

        self::assertInstanceOf(ExtraItemResult::class, $extraItemResult);
        self::assertEquals($firstExtra->id, $extraItemResult->id);
    }
}
