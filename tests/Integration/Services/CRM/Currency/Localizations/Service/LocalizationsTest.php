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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Currency\Localizations\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Currency\Localizations\Service\Localizations;
use Bitrix24\SDK\Services\CRM\Currency\Localizations\Result\LocalizationItemResult;
use Bitrix24\SDK\Services\CRM\Currency\Service\Currency;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class LocalizationsTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Currency\Localizations\Service
 */
#[CoversMethod(Localizations::class,'set')]
#[CoversMethod(Localizations::class,'get')]
#[CoversMethod(Localizations::class,'fields')]
#[CoversMethod(Localizations::class,'delete')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Currency\Localizations\Service\Localizations::class)]
class LocalizationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    public const CURRENCY_CODE = 'XXX';

    protected ServiceBuilder $sb;

    protected Currency $currencyService;

    protected Localizations $localizationsService;

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->currencyService = $this->sb->getCRMScope()->currency();
        $this->localizationsService = $this->sb->getCRMScope()->localizations();
        $fields = [
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
        $this->currencyService->add($fields);
    }

    protected function tearDown(): void
    {
        $this->currencyService->delete(self::CURRENCY_CODE);
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $propListFromApi = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($this->localizationsService->fields()->getFieldsDescription()));
        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, LocalizationItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation():void
    {
        $allFields = $this->localizationsService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new Core\Fields\FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            LocalizationItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSet(): void
    {
        $fields = [
            'en' => $this->getLocalizationFields('en'),
            'nl' => $this->getLocalizationFields('nl'),
        ];
        self::assertEquals(
            1,
            $this->localizationsService->set(self::CURRENCY_CODE, $fields)->getId()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $fields = [
            'en' => $this->getLocalizationFields('en'),
            'de' => $this->getLocalizationFields('de'),
        ];
        $this->localizationsService->set(self::CURRENCY_CODE, $fields)->getCoreResponse()->getResponseData()->getResult();

        self::assertTrue(
            $this->localizationsService->delete(
                self::CURRENCY_CODE,
                ['de']
            )->isSuccess()
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testFields(): void
    {
        self::assertIsArray($this->localizationsService->fields()->getFieldsDescription());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        self::assertIsArray($this->localizationsService->get(self::CURRENCY_CODE)->getCoreResponse()->getResponseData()->getResult()['en']);
    }

    protected function getLocalizationFields(string $lang): array {
        return [
            'DECIMALS'=> 2,
            'DEC_POINT' => '.',
            'FORMAT_STRING' => '$#',
            'FULL_NAME' => 'Test currency '.$lang,
            'HIDE_ZERO' => 'N',
            'THOUSANDS_SEP' => ' ',
            'THOUSANDS_VARIANT' => 'S',
        ];
    }
}