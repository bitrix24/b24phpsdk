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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\VatRates\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\VatRates\Result\VatRateItemResult;
use Bitrix24\SDK\Services\CRM\VatRates\Service\Vat;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use MoneyPHP\Percentage\Percentage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vat::class)]
#[CoversMethod(Vat::class, 'fields')]
#[CoversMethod(Vat::class, 'add')]
#[CoversMethod(Vat::class, 'get')]
#[CoversMethod(Vat::class, 'delete')]
#[CoversMethod(Vat::class, 'list')]
#[CoversMethod(Vat::class, 'update')]
class VatTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $addedVatRates = [];

    protected function tearDown(): void
    {
        foreach ($this->addedVatRates as $addedVatRate) {
            $this->sb->getCRMScope()->vat()->delete($addedVatRate);
        }
    }

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('crm.vat.fields')]
    public function testFields(): void
    {
        self::assertIsArray($this->sb->getCRMScope()->vat()->fields()->getFieldsDescription());
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(
            array_keys($this->sb->getCRMScope()->vat()->fields()->getFieldsDescription())
        );
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, VatRateItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->sb->getCRMScope()->vat()->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            VatRateItemResult::class
        );
    }

    public function testAddAndGet(): void
    {
        $name = sprintf('test vat name %s', time());
        $percentage = new Percentage((string)random_int(1, 99));
        $sort = random_int(1, 500);
        $isActive = (bool)random_int(0, 1);

        $newVatRateId = $this->sb->getCRMScope()->vat()->add($name, $percentage, $sort, $isActive)->getId();
        $this->addedVatRates[] = $newVatRateId;

        $vatRateItemResult = $this->sb->getCRMScope()->vat()->get($newVatRateId)->getRate();

        $this->assertEquals($name, $vatRateItemResult->NAME);
        $this->assertTrue($percentage->equals($vatRateItemResult->RATE));
        $this->assertEquals($sort, $vatRateItemResult->C_SORT);
        $this->assertEquals($isActive, $vatRateItemResult->ACTIVE);
    }

    public function testDelete(): void
    {
        $newVatRateId = $this->sb->getCRMScope()->vat()->add(
            sprintf('test vat name %s', time()),
            new Percentage((string)random_int(1, 99))
        )->getId();
        $this->assertTrue($this->sb->getCRMScope()->vat()->delete($newVatRateId)->isSuccess());

        $items = array_column($this->sb->getCRMScope()->vat()->list([], [], ['ID'])->getRates(), 'ID');
        $this->assertFalse(in_array($newVatRateId, $items, true));
    }

    public function testUpdateWithoutParameters(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        $this->sb->getCRMScope()->vat()->update(1);
    }

    public function testUpdate(): void
    {
        $title = sprintf('test vat name %s', time());
        $percentage = new Percentage((string)random_int(20, 30));
        $newVatRateId = $this->sb->getCRMScope()->vat()->add($title, $percentage)->getId();
        $this->addedVatRates[] = $newVatRateId;

        $newTitle = 'new' . $title;
        $this->assertTrue(
            $this->sb->getCRMScope()->vat()->update(
                $newVatRateId,
                $newTitle
            )->isSuccess()
        );

        $vatRateItemResult = $this->sb->getCRMScope()->vat()->get($newVatRateId)->getRate();
        $this->assertEquals($newTitle, $vatRateItemResult->NAME);
    }

    public function testList(): void
    {
        $newVatRateId = $this->sb->getCRMScope()->vat()->add(
            sprintf('test vat name %s', time()),
            new Percentage((string)random_int(1, 99))
        )->getId();

        $items = array_column($this->sb->getCRMScope()->vat()->list([], [], ['ID'])->getRates(), 'ID');
        $this->assertTrue(in_array($newVatRateId, $items, true));
    }
}