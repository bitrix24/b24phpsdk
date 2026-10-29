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

use Bitrix24\SDK\Services\Catalog\V3\Catalog\Service\CatalogFilter;
use Bitrix24\SDK\Tests\Integration\Factory;

/**
 * Finds the product information block of the test portal's trade catalog.
 */
trait ProductCatalogIblockTrait
{
    protected function getProductCatalogIblockId(): int
    {
        // a product catalog (not an offers catalog) has no parent product iblock
        $filter = new CatalogFilter();
        $filter->productIblockId()->eq(0);

        $catalogs = Factory::getServiceBuilder()->getCatalogScope()->v3()->catalog()->list($filter)->getCatalogs();
        if ($catalogs === []) {
            self::markTestSkipped('test portal has no product catalog');
        }

        return (int)$catalogs[0]->iblockId;
    }
}
