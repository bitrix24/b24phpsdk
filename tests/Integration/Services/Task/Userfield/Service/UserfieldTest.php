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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\Userfield\Service;

use Bitrix24\SDK\Services\Task\Userfield\Service\Userfield;
use Bitrix24\SDK\Tests\Builders\Services\CRM\Userfield\SystemUserfieldBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Userfield::class)]
#[CoversMethod(Userfield::class, 'add')]
#[CoversMethod(Userfield::class, 'get')]
#[CoversMethod(Userfield::class, 'getlist')]
#[CoversMethod(Userfield::class, 'delete')]
#[CoversMethod(Userfield::class, 'update')]
#[CoversMethod(Userfield::class, 'getTypes')]
#[CoversMethod(Userfield::class, 'getFields')]
class UserfieldTest extends TestCase
{
    protected Userfield $userfieldService;
    
    protected function setUp(): void
    {
        $this->userfieldService = Factory::getServiceBuilder()->getTaskScope()->userfield();
        $this->userfieldService->getList([], []);
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
        $taskUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfield();
        $this->assertEquals($newUserfieldId, $taskUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $taskUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_' . $newUserFieldItem['FIELD_NAME'], $taskUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $taskUserfieldItemResult->XML_ID);
    }

    #[DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testUpdate(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->userfieldService->add($newUserFieldItem)->getId();
        $taskUserfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfield();
        $this->assertEquals($newUserfieldId, $taskUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $taskUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_' . $newUserFieldItem['FIELD_NAME'], $taskUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $taskUserfieldItemResult->XML_ID);

        $this->assertTrue(
            $this->userfieldService->update(
                $newUserfieldId,
                [
                    'EDIT_FORM_LABEL' => $newUserFieldItem['EDIT_FORM_LABEL']['en'] . 'QQQ',
                ]
            )->isSuccess()
        );

        $userfieldItemResult = $this->userfieldService->get($newUserfieldId)->userfield();
        $this->assertEquals($taskUserfieldItemResult->EDIT_FORM_LABEL['en'] . 'QQQ', $userfieldItemResult->EDIT_FORM_LABEL['en']);
    }

    public function testList(): void
    {
        $taskUserfieldsResult = $this->userfieldService->getList([], []);
        $this->assertGreaterThanOrEqual(0, count($taskUserfieldsResult->getUserfields()));
    }
    
    public function testGetTypes(): void
    {
        $userfieldTypesResult = $this->userfieldService->getTypes();
        self::assertIsArray($userfieldTypesResult->getTypes());
    }
    
    public function testGetFields(): void
    {
        $userfieldFieldsResult = $this->userfieldService->getFields();
        self::assertIsArray($userfieldFieldsResult->getFields());
    }

}
