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

namespace Bitrix24\SDK\Services\Catalog;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Catalog;

#[ApiServiceBuilderMetadata(new Scope(['catalog']))]
class CatalogServiceBuilder extends AbstractServiceBuilder
{
    public function product(): Catalog\Product\Service\Product
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Product\Service\Product(
                new Catalog\Product\Service\Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function catalog(): Catalog\Catalog\Service\Catalog
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Catalog\Service\Catalog(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productPropertyFeature(): Catalog\ProductPropertyFeature\Service\ProductPropertyFeature
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            // Use specialized Batch for ProductPropertyFeature to ensure correct REST parameter mapping
            // (lowercase 'id' key, unlike the base Batch default of uppercase 'ID')
            $productPropertyFeatureBatch = new Catalog\ProductPropertyFeature\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Catalog\ProductPropertyFeature\Service\ProductPropertyFeature(
                new Catalog\ProductPropertyFeature\Service\Batch($productPropertyFeatureBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
