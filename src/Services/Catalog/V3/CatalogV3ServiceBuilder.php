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

namespace Bitrix24\SDK\Services\Catalog\V3;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

/**
 * REST API v3 services of the catalog scope; legacy REST v1 services stay in CatalogServiceBuilder.
 */
#[ApiServiceBuilderMetadata(new Scope(['catalog']))]
class CatalogV3ServiceBuilder extends AbstractServiceBuilder
{
    public function vat(): Vat\Service\Vat
    {
        $this->serviceCache[__METHOD__] ??= new Vat\Service\Vat($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function vatField(): VatField\Service\VatField
    {
        $this->serviceCache[__METHOD__] ??= new VatField\Service\VatField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function measure(): Measure\Service\Measure
    {
        $this->serviceCache[__METHOD__] ??= new Measure\Service\Measure($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function measureField(): MeasureField\Service\MeasureField
    {
        $this->serviceCache[__METHOD__] ??= new MeasureField\Service\MeasureField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function section(): Section\Service\Section
    {
        $this->serviceCache[__METHOD__] ??= new Section\Service\Section($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function sectionField(): SectionField\Service\SectionField
    {
        $this->serviceCache[__METHOD__] ??= new SectionField\Service\SectionField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function catalog(): Catalog\Service\Catalog
    {
        $this->serviceCache[__METHOD__] ??= new Catalog\Service\Catalog($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function catalogField(): CatalogField\Service\CatalogField
    {
        $this->serviceCache[__METHOD__] ??= new CatalogField\Service\CatalogField($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }
}
