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

    public function product(): Catalog\Product\Service\Product
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $productBatch = new Catalog\Product\Batch($this->core, $this->log);
            $this->serviceCache[__METHOD__] = new Catalog\Product\Service\Product(
                new Catalog\Product\Service\Batch($productBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productService(): Catalog\Product\ProductService\Service\ProductService
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Product\ProductService\Service\ProductService(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productSku(): Catalog\Product\Sku\Service\Sku
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Product\Sku\Service\Sku(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productOffer(): Catalog\Product\Offer\Service\Offer
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Product\Offer\Service\Offer(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
  
    public function catalogEnum(): Catalog\Enum\Service\CatalogEnum
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Enum\Service\CatalogEnum(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function price(): Catalog\Price\Service\Price
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Price\Service\Price(
                new Catalog\Price\Service\Batch(
                    new Catalog\Price\Batch($this->core, $this->log),
                    $this->log
                ),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function extra(): Catalog\Extra\Service\Extra
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Extra\Service\Extra(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productImage(): Catalog\ProductImage\Service\ProductImage
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $productImageBatch = new Catalog\ProductImage\Batch($this->core, $this->log);
            $this->serviceCache[__METHOD__] = new Catalog\ProductImage\Service\ProductImage(
                new Catalog\ProductImage\Service\Batch($productImageBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function measure(): Catalog\Measure\Service\Measure
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\Measure\Service\Measure(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function priceType(): Catalog\PriceType\Service\PriceType
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\PriceType\Service\PriceType(
                new Catalog\PriceType\Service\Batch(
                    new Catalog\PriceType\Batch($this->core, $this->log),
                    $this->log
                ),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productPropertyEnum(): Catalog\ProductPropertyEnum\Service\ProductPropertyEnum
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $productPropertyEnumBatch = new Catalog\ProductPropertyEnum\Batch($this->core, $this->log);
            $this->serviceCache[__METHOD__] = new Catalog\ProductPropertyEnum\Service\ProductPropertyEnum(
                new Catalog\ProductPropertyEnum\Service\Batch($productPropertyEnumBatch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function priceTypeLang(): Catalog\PriceTypeLang\Service\PriceTypeLang
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\PriceTypeLang\Service\PriceTypeLang(
                new Catalog\PriceTypeLang\Service\Batch(
                    new Catalog\PriceTypeLang\Batch($this->core, $this->log),
                    $this->log
                ),
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

    public function productProperty(): Catalog\ProductProperty\Service\ProductProperty
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Catalog\ProductProperty\Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Catalog\ProductProperty\Service\ProductProperty(
                new Catalog\ProductProperty\Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function priceTypeGroup(): Catalog\PriceTypeGroup\Service\PriceTypeGroup
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\PriceTypeGroup\Service\PriceTypeGroup(
                new Catalog\PriceTypeGroup\Service\Batch(
                    new Catalog\PriceTypeGroup\Batch($this->core, $this->log),
                    $this->log
                ),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function productPropertySection(): Catalog\ProductPropertySection\Service\ProductPropertySection
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Catalog\ProductPropertySection\Service\ProductPropertySection(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }  
}
