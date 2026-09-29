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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Measure\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\AddedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\DeletedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\MeasuresResult;
use Bitrix24\SDK\Services\Catalog\Measure\Result\UpdatedMeasureResult;
use Bitrix24\SDK\Services\Catalog\Measure\Service\Measure;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Measure::class)]
class MeasureTest extends TestCase
{
    private Measure $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Measure(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testAddReturnsAddedMeasureResult(): void
    {
        $this->assertInstanceOf(
            AddedMeasureResult::class,
            $this->service->add(['code' => 715, 'measureTitle' => 'Pair'])
        );
    }

    #[Test]
    public function testUpdateReturnsUpdatedMeasureResult(): void
    {
        $this->assertInstanceOf(
            UpdatedMeasureResult::class,
            $this->service->update(1, ['measureTitle' => 'Pair'])
        );
    }

    #[Test]
    public function testGetReturnsMeasureResult(): void
    {
        $this->assertInstanceOf(MeasureResult::class, $this->service->get(1));
    }

    #[Test]
    public function testListReturnsMeasuresResult(): void
    {
        $this->assertInstanceOf(MeasuresResult::class, $this->service->list());
    }

    #[Test]
    public function testDeleteReturnsDeletedMeasureResult(): void
    {
        $this->assertInstanceOf(DeletedMeasureResult::class, $this->service->delete(1));
    }

    #[Test]
    public function testFieldsReturnsFieldsResult(): void
    {
        $this->assertInstanceOf(FieldsResult::class, $this->service->fields());
    }

    #[Test]
    public function testGetThrowsOnNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->get(0);
    }

    #[Test]
    public function testUpdateThrowsOnNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->update(0, []);
    }

    #[Test]
    public function testDeleteThrowsOnNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->delete(0);
    }
}
