<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Currency\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Currency\Service\Currency;
use Bitrix24\SDK\Services\CRM\Currency\Result\CurrencyItemResult;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class CurrencyTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Currency\Service
 */
#[CoversMethod(Currency::class,'add')]
#[CoversMethod(Currency::class,'delete')]
#[CoversMethod(Currency::class,'list')]
#[CoversMethod(Currency::class,'fields')]
#[CoversMethod(Currency::class,'get')]
#[CoversMethod(Currency::class,'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Currency\Service\Currency::class)]
class CurrencyTest extends TestCase
{
    use CustomBitrix24Assertions;

    public const CURRENCY_CODE = 'XXX';

    protected ServiceBuilder $sb;

    protected Currency $currencyService;

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->currencyService = $this->sb->getCRMScope()->currency();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->currencyService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, CurrencyItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {

        $allFields = $this->currencyService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            CurrencyItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $fields = $this->getCurrencyFields();
        self::assertEquals(
            self::CURRENCY_CODE,
            $this->currencyService->add($fields)->getCoreResponse()->getResponseData()->getResult()[0]
        );

        $this->currencyService->delete(self::CURRENCY_CODE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $fields = $this->getCurrencyFields();
        $this->currencyService->add($fields);

        self::assertTrue(
            $this->currencyService->delete(
                self::CURRENCY_CODE
            )->isSuccess()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->currencyService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $fields = $this->getCurrencyFields();
        $this->currencyService->add($fields);
        self::assertEquals(100, $this->currencyService->get(self::CURRENCY_CODE)->getCoreResponse()->getResponseData()->getResult()['SORT']);

        $this->currencyService->delete(self::CURRENCY_CODE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $fields = $this->getCurrencyFields();
        $this->currencyService->add($fields);
        self::assertGreaterThanOrEqual(1, $this->currencyService->list([])->getCurrencies());

        $this->currencyService->delete(self::CURRENCY_CODE);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $fields = $this->getCurrencyFields();
        $this->currencyService->add($fields);
        $currencyCode = self::CURRENCY_CODE;
        $newFields = $fields;
        $sort = 500;
        $newFields['SORT'] = $sort;

        self::assertTrue($this->currencyService->update(
            $currencyCode,
            $newFields
        )->isSuccess());
        $response = $this->currencyService->get(
            $currencyCode
        )->getCoreResponse()->getResponseData()->getResult()['SORT'];
        self::assertEquals($sort, $response);

        $this->currencyService->delete($currencyCode);
    }

    protected function getCurrencyFields(): array {
        return [
            'CURRENCY' => self::CURRENCY_CODE,
            'BASE' => 'N',
            'AMOUNT_CNT' => 1,
            'AMOUNT' => 100.0,
            'SORT' => 100,
            'LANG' => [
                'en' => [
                    'DECIMALS'=> 2,
                    'DEC_POINT' => '.',
                    'FORMAT_STRING' => '#$',
                    'FULL_NAME' => 'Test currency',
                    'HIDE_ZERO' => 'N',
                    'THOUSANDS_SEP' => ' ',
                    'THOUSANDS_VARIANT' => 'S',
                ],
            ],
        ];
    }
}