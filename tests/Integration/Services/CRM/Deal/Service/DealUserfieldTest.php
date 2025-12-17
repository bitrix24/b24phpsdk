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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;

use Bitrix24\SDK\Services\CRM\Deal\Service\DealUserfield;
use Bitrix24\SDK\Tests\Builders\Services\CRM\Userfield\SystemUserfieldBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DealUserfield::class)]
#[CoversMethod(DealUserfield::class, 'add')]
#[CoversMethod(DealUserfield::class, 'get')]
#[CoversMethod(DealUserfield::class, 'list')]
#[CoversMethod(DealUserfield::class, 'delete')]
#[CoversMethod(DealUserfield::class, 'update')]
class DealUserfieldTest extends TestCase
{
    protected DealUserfield $userfieldService;

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
        $dealUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $dealUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $dealUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $dealUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $dealUserfieldItemResult->XML_ID);
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testUpdate(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->userfieldService->add($newUserFieldItem)->getId();
        $dealUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $dealUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $dealUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $dealUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $dealUserfieldItemResult->XML_ID);

        $this->assertTrue(
            $this->userfieldService->update(
                $newUserfieldId,
                [
                    'EDIT_FORM_LABEL' => $newUserFieldItem['EDIT_FORM_LABEL']['en'] . 'QQQ',
                ]
            )->isSuccess()
        );

        $ufFieldAfter = $this->userfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($dealUserfieldItemResult->EDIT_FORM_LABEL['en'] . 'QQQ', $ufFieldAfter->EDIT_FORM_LABEL['en']);
    }

    public function testList(): void
    {
        $dealUserfieldsResult = $this->userfieldService->list([], []);
        $this->assertGreaterThanOrEqual(0, count($dealUserfieldsResult->getUserfields()));
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->userfieldService = Factory::getServiceBuilder()->getCRMScope()->dealUserfield();
    }
}