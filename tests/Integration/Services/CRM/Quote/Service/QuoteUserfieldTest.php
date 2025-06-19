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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service;

use Bitrix24\SDK\Services\CRM\Quote\Service\QuoteUserfield;
use Bitrix24\SDK\Tests\Builders\Services\CRM\Userfield\SystemUserfieldBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(QuoteUserfield::class)]
#[CoversMethod(QuoteUserfield::class, 'add')]
#[CoversMethod(QuoteUserfield::class, 'get')]
#[CoversMethod(QuoteUserfield::class, 'list')]
#[CoversMethod(QuoteUserfield::class, 'delete')]
#[CoversMethod(QuoteUserfield::class, 'update')]
class QuoteUserfieldTest extends TestCase
{
    protected QuoteUserfield $userfieldService;
    
    protected function setUp(): void
    {
        $this->userfieldService = Fabric::getServiceBuilder()->getCRMScope()->quoteUserfield();
    }

    /**
     * @throws \Exception
     */
    public static function systemUserfieldsDemoDataDataProvider(): Generator
    {
        yield 'user type id string' => [
            (new SystemUserfieldBuilder())->build(),
        ];

        mt_srand();
        yield 'user type id integer' => [
            (new SystemUserfieldBuilder('integer'))->build(),
        ];
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testAdd(array $newUserFieldItem): void
    {
        self::assertGreaterThanOrEqual(1, $this->userfieldService->add($newUserFieldItem)->getId());
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testDelete(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->userfieldService->add($newUserFieldItem)->getId();
        $this->assertTrue($this->userfieldService->delete($newUserfieldId)->isSuccess());
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testGet(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->userfieldService->add($newUserFieldItem)->getId();
        $quoteUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $quoteUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $quoteUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $quoteUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $quoteUserfieldItemResult->XML_ID);
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testUpdate(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->userfieldService->add($newUserFieldItem)->getId();
        $quoteUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $quoteUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $quoteUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $quoteUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $quoteUserfieldItemResult->XML_ID);

        $this->assertTrue(
            $this->userfieldService->update(
                $newUserfieldId,
                [
                    'EDIT_FORM_LABEL' => $newUserFieldItem['EDIT_FORM_LABEL']['en'] . 'QQQ',
                ]
            )->isSuccess()
        );

        $ufFieldAfter = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($quoteUserfieldItemResult->EDIT_FORM_LABEL['en'] . 'QQQ', $ufFieldAfter->EDIT_FORM_LABEL['en']);
    }

    public function testList(): void
    {
        $quoteUserfieldsResult = $this->userfieldService->list([], []);
        $this->assertGreaterThanOrEqual(0, count($quoteUserfieldsResult->getUserfields()));
    }

}
