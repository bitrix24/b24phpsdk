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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Contact\Service;

use Bitrix24\SDK\Services\CRM\Contact\Service\ContactUserfield;
use Bitrix24\SDK\Tests\Integration\Factory;
use Generator;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Contact\Service\ContactUserfield::class)]
class ContactUserfieldTest extends TestCase
{
    protected ContactUserfield $contactUserfieldService;

    /**
     * @throws \Exception
     */
    public static function systemUserfieldsDemoDataDataProvider(): Generator
    {
        yield 'user type id string' => [
            [
                'FIELD_NAME'        => sprintf('%s%s', substr((string)random_int(0, PHP_INT_MAX), 0, 3), time()),
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'тест uf тип string',
                    'en' => 'test uf type string',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'тест uf тип string',
                    'en' => 'test uf type string',
                ],
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => 'b24phpsdk_type_string',
                'SETTINGS'          => [],
            ],
        ];

        mt_srand();
        yield 'user type id integer' => [
            [
                'FIELD_NAME'        => sprintf('%s%s', substr((string)random_int(0, PHP_INT_MAX), 0, 3), time()),
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'тест uf тип integer',
                    'en' => 'test uf type integer',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'тест uf тип integer',
                    'en' => 'test uf type integer',
                ],
                'USER_TYPE_ID'      => 'integer',
                'XML_ID'            => 'b24phpsdk_type_integer',
                'SETTINGS'          => [],
            ],
        ];
    }

    /**
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testAdd(array $newUserFieldItem): void
    {
        self::assertGreaterThanOrEqual(1, $this->contactUserfieldService->add($newUserFieldItem)->getId());
    }

    
    #[\PHPUnit\Framework\Attributes\DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testDelete(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->contactUserfieldService->add($newUserFieldItem)->getId();
        $this->assertTrue($this->contactUserfieldService->delete($newUserfieldId)->isSuccess());
    }

    
    #[\PHPUnit\Framework\Attributes\DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testGet(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->contactUserfieldService->add($newUserFieldItem)->getId();
        $contactUserfieldItemResult = $this->contactUserfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $contactUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $contactUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $contactUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $contactUserfieldItemResult->XML_ID);
    }

    
    #[\PHPUnit\Framework\Attributes\DataProvider('systemUserfieldsDemoDataDataProvider')]
    public function testUpdate(array $newUserFieldItem): void
    {
        $newUserfieldId = $this->contactUserfieldService->add($newUserFieldItem)->getId();
        $contactUserfieldItemResult = $this->contactUserfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($newUserfieldId, $contactUserfieldItemResult->ID);
        $this->assertEquals($newUserFieldItem['USER_TYPE_ID'], $contactUserfieldItemResult->USER_TYPE_ID);
        $this->assertEquals('UF_CRM_' . $newUserFieldItem['FIELD_NAME'], $contactUserfieldItemResult->FIELD_NAME);
        $this->assertEquals($newUserFieldItem['XML_ID'], $contactUserfieldItemResult->XML_ID);

        $this->assertTrue(
            $this->contactUserfieldService->update(
                $newUserfieldId,
                [
                    'EDIT_FORM_LABEL' => $newUserFieldItem['EDIT_FORM_LABEL']['en'] . 'QQQ',
                ]
            )->isSuccess()
        );

        $ufFieldAfter = $this->contactUserfieldService->get($newUserfieldId)->userfieldItem();
        $this->assertEquals($contactUserfieldItemResult->EDIT_FORM_LABEL['en'] . 'QQQ', $ufFieldAfter->EDIT_FORM_LABEL['en']);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     */
    public function testList(): void
    {
        $contactUserfieldsResult = $this->contactUserfieldService->list([], []);
        $this->assertGreaterThanOrEqual(0, count($contactUserfieldsResult->getUserfields()));
    }

    protected function setUp(): void
    {
        $this->contactUserfieldService = Factory::getServiceBuilder()->getCRMScope()->contactUserfield();
    }
}