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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Services\Catalog\CatalogServiceBuilder;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Measure as LegacyMeasure;
use Bitrix24\SDK\Services\Catalog\V3\CatalogV3ServiceBuilder;
use Bitrix24\SDK\Services\Catalog\V3\Measure\Service\Measure;
use Bitrix24\SDK\Services\Catalog\V3\MeasureField\Service\MeasureField;
use Bitrix24\SDK\Services\Catalog\V3\Vat\Service\Vat;
use Bitrix24\SDK\Services\Catalog\V3\VatField\Service\VatField;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Vat as LegacyVat;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(CatalogV3ServiceBuilder::class)]
#[CoversClass(CatalogServiceBuilder::class)]
class CatalogV3ServiceBuilderTest extends TestCase
{
    #[Test]
    public function testAllServiceAccessorsAreTypedAndCached(): void
    {
        $builder = $this->catalogBuilder()->v3();

        foreach (['vat' => Vat::class, 'vatField' => VatField::class, 'measure' => Measure::class, 'measureField' => MeasureField::class] as $accessor => $class) {
            $this->assertInstanceOf($class, $builder->$accessor());
            $this->assertSame($builder->$accessor(), $builder->$accessor());
        }

        $metadata = (new \ReflectionClass(CatalogV3ServiceBuilder::class))->getAttributes(ApiServiceBuilderMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
    }

    #[Test]
    public function testCatalogBuilderExposesCachedV3BuilderAndKeepsLegacyServices(): void
    {
        $catalog = $this->catalogBuilder();

        $this->assertSame($catalog->v3(), $catalog->v3());
        $this->assertInstanceOf(LegacyVat::class, $catalog->vat());
        $this->assertInstanceOf(LegacyMeasure::class, $catalog->measure());
    }

    private function catalogBuilder(): CatalogServiceBuilder
    {
        return new CatalogServiceBuilder(
            new NullCore(),
            $this->createStub(BatchOperationsInterface::class),
            $this->createStub(BulkItemsReaderInterface::class),
            new NullLogger()
        );
    }
}
