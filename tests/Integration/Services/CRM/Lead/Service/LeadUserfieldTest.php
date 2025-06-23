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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service;

use Bitrix24\SDK\Services\CRM\Lead\Service\LeadUserfield;
use Bitrix24\SDK\Tests\Builders\Services\CRM\Userfield\SystemUserfieldBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LeadUserfield::class)]
#[CoversMethod(LeadUserfield::class, 'add')]
#[CoversMethod(LeadUserfield::class, 'get')]
#[CoversMethod(LeadUserfield::class, 'list')]
#[CoversMethod(LeadUserfield::class, 'delete')]
#[CoversMethod(LeadUserfield::class, 'update')]
class LeadUserfieldTest extends TestCase
{
    protected LeadUserfield $userfieldService;

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
        $leadUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $leadUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $leadUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $leadUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $leadUserfieldItemResult->XML_ID);
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testUpdate(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->userfieldService->add($newUserFieldItem)->getId();
        $leadUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $leadUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $leadUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $leadUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $leadUserfieldItemResult->XML_ID);

        $this->assertTrue(
            $this->userfieldService->update(
                $newUserfieldId,
                [
                    'EDIT_FORM_LABEL' => $newUserFieldItem['EDIT_FORM_LABEL']['en'] . 'QQQ',
                ]
            )->isSuccess()
        );

        $ufFieldAfter = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($leadUserfieldItemResult->EDIT_FORM_LABEL['en'] . 'QQQ', $ufFieldAfter->EDIT_FORM_LABEL['en']);
    }

    public function testList(): void
    {
        $leadUserfieldsResult = $this->userfieldService->list([], []);
        $this->assertGreaterThanOrEqual(0, count($leadUserfieldsResult->getUserfields()));
    }

    protected function setUp(): void
    {
        $this->userfieldService = Fabric::getServiceBuilder()->getCRMScope()->leadUserfield();
    }
}