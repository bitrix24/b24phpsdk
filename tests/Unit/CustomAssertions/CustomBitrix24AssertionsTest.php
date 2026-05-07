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

namespace Bitrix24\SDK\Tests\Unit\CustomAssertions;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Generator;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CustomBitrix24Assertions::class)]
class CustomBitrix24AssertionsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private const array VALID_DATA = [
        'stringField'   => 'hello',
        'boolField'     => true,
        'intField'      => 42,
        'arrayField'    => ['a', 'b'],
        'nullableString' => null,
        'nullableArray'  => null,
    ];

    #[Test]
    public function testPassesWhenAllTypesMatchAnnotations(): void
    {
        $allTypesStubItem = new AllTypesStubItem(self::VALID_DATA);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($allTypesStubItem, AllTypesStubItem::class);
    }

    #[Test]
    public function testPassesWhenNullableFieldsHaveValues(): void
    {
        $allTypesStubItem = new AllTypesStubItem(array_merge(self::VALID_DATA, [
            'nullableString' => 'world',
            'nullableArray'  => [1, 2, 3],
        ]));
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($allTypesStubItem, AllTypesStubItem::class);
    }

    #[Test]
    #[DataProvider('wrongTypesDataProvider')]
    public function testFailsWhenFieldHasWrongType(array $data): void
    {
        $this->expectException(AssertionFailedError::class);
        $allTypesStubItem = new AllTypesStubItem($data);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($allTypesStubItem, AllTypesStubItem::class);
    }

    public static function wrongTypesDataProvider(): Generator
    {
        yield 'string where bool expected'         => [array_merge(self::VALID_DATA, ['boolField'     => 'true'])];
        yield 'int where bool expected'            => [array_merge(self::VALID_DATA, ['boolField'     => 1])];
        yield 'bool where string expected'         => [array_merge(self::VALID_DATA, ['stringField'   => true])];
        yield 'int where string expected'          => [array_merge(self::VALID_DATA, ['stringField'   => 42])];
        yield 'string where int expected'          => [array_merge(self::VALID_DATA, ['intField'      => '42'])];
        yield 'bool where int expected'            => [array_merge(self::VALID_DATA, ['intField'      => false])];
        yield 'string where array expected'        => [array_merge(self::VALID_DATA, ['arrayField'    => 'arr'])];
        yield 'int for non-null nullable string'   => [array_merge(self::VALID_DATA, ['nullableString' => 42])];
        yield 'string for non-null nullable array' => [array_merge(self::VALID_DATA, ['nullableArray'  => 'arr'])];
    }
}

/**
 * @property-read string      $stringField
 * @property-read bool        $boolField
 * @property-read int         $intField
 * @property-read array       $arrayField
 * @property-read string|null $nullableString
 * @property-read array|null  $nullableArray
 */
class AllTypesStubItem extends AbstractItem {}
