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

namespace Bitrix24\SDK\Tests\Unit\Core\Fields;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FieldsFilter::class)]
class FieldsFilterTest extends TestCase
{
    #[Test]
    #[DataProvider('smartProcessFieldsDataProvider')]
    public function testFilterSmartProcessFields(array $all, array $result): void
    {
        $this->assertEquals((new FieldsFilter())->filterSmartProcessFields($all), $result);
    }

    #[Test]
    #[DataProvider('systemFieldsDataProvider')]
    public function testFilterSystemFields(array $all, array $result): void
    {
        $this->assertEquals((new FieldsFilter())->filterSystemFields($all), $result);
    }

    #[Test]
    #[DataProvider('userFieldsDataProvider')]
    public function testFilterUserFields(array $all, array $result): void
    {
        $this->assertEquals((new FieldsFilter())->filterUserFields($all), $result);
    }

    public static function smartProcessFieldsDataProvider(): Generator
    {
        yield 'empty' => [
            [
            ],
            [
            ],
        ];
        yield 'UF' => [
            [
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
            [
            ],
        ];
        yield 'system fields' => [
            [
                'ID',
                'NAME'
            ],
            [
            ],
        ];
        yield 'system fields + UF' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
            [
            ],
        ];
        yield 'system fields + UF + smart process' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442',
                'PARENT_ID_1032'
            ],
            [
                'PARENT_ID_1032'
            ],
        ];
        yield 'smart process' => [
            [
                'PARENT_ID_1032',
                'PARENT_ID_1031'
            ],
            [
                'PARENT_ID_1032',
                'PARENT_ID_1031'
            ],
        ];
    }

    public static function userFieldsDataProvider(): Generator
    {
        yield 'empty' => [
            [
            ],
            [
            ],
        ];
        yield 'UF' => [
            [
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
            [
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
        ];
        yield 'system fields' => [
            [
                'ID',
                'NAME'
            ],
            [
            ],
        ];
        yield 'system fields + UF' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
            [
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
        ];
        yield 'system fields + UF + smart process' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442',
                'PARENT_ID_1032'
            ],
            [
                'UF_CRM_1728045441',
                'UF_CRM_1728045442',
            ],
        ];
    }

    public static function systemFieldsDataProvider(): Generator
    {
        yield 'empty' => [
            [
            ],
            [
            ],
        ];
        yield 'product fields' => [
            [
                'PROPERTY_106',
                'PROPERTY_108',
            ],
            [
            ],
        ];
        yield 'UF' => [
            [
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
            [
            ],
        ];
        yield 'system fields' => [
            [
                'ID',
                'NAME'
            ],
            [
                'ID',
                'NAME'
            ],
        ];
        yield 'system fields + UF' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442'
            ],
            [
                'ID',
                'NAME'
            ],
        ];
        yield 'system fields + UF + smart process' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442',
                'PARENT_ID_1032'
            ],
            [
                'ID',
                'NAME'
            ],
        ];
        yield 'system fields + UF + smart process + product fields' => [
            [
                'ID',
                'NAME',
                'UF_CRM_1728045441',
                'UF_CRM_1728045442',
                'PARENT_ID_1032',
                'PROPERTY_106',
                'PROPERTY_108',
            ],
            [
                'ID',
                'NAME'
            ],
        ];
    }
}
