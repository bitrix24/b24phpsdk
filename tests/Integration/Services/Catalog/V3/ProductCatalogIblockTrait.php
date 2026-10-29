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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\V3;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Tests\Integration\Factory;

/**
 * Finds the product information block of the test portal's trade catalog.
 */
trait ProductCatalogIblockTrait
{
    protected function getProductCatalogIblockId(): int
    {
        // a product catalog (not a SKU catalog) has no parent product iblock
        $catalogs = Factory::getCore()->call(
            'catalog.catalog.list',
            ['select' => ['iblockId', 'productIblockId']],
            ApiVersion::v3
        )->getResponseData()->getResult()['items'];

        foreach ($catalogs as $catalog) {
            if ((int)$catalog['productIblockId'] === 0) {
                return (int)$catalog['iblockId'];
            }
        }

        self::markTestSkipped('test portal has no product catalog');
    }
}
