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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Enum\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Enum\Result\RoundTypeItemResult;
use Bitrix24\SDK\Services\Catalog\Enum\Result\StoreDocumentTypeItemResult;
use Bitrix24\SDK\Services\Catalog\Enum\Service\CatalogEnum;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(CatalogEnum::class, 'getRoundTypes')]
#[CoversMethod(CatalogEnum::class, 'getStoreDocumentTypes')]
class CatalogEnumTest extends TestCase
{
    private CatalogEnum $catalogEnumService;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogEnumService = Factory::getServiceBuilder()->getCatalogScope()->catalogEnum();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetRoundTypes(): void
    {
        $roundTypes = $this->catalogEnumService->getRoundTypes()->getRoundTypes();

        self::assertNotEmpty($roundTypes);
        self::assertInstanceOf(RoundTypeItemResult::class, $roundTypes[0]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetStoreDocumentTypes(): void
    {
        $storeDocumentTypes = $this->catalogEnumService->getStoreDocumentTypes()->getStoreDocumentTypes();

        self::assertNotEmpty($storeDocumentTypes);
        self::assertInstanceOf(StoreDocumentTypeItemResult::class, $storeDocumentTypes[0]);
    }
}
