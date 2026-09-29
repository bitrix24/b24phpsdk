<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigEnumItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigItemResult;
use PHPUnit\Framework\TestCase;

class UserfieldConfigItemResultTest extends TestCase
{
    public function testEnumRowsAreTypedResults(): void
    {
        $userfieldConfigItemResult = new UserfieldConfigItemResult(['enum' => [
            ['id' => 1, 'value' => 'First', 'def' => 'Y'],
            ['id' => 2, 'value' => 'Second', 'def' => 'N'],
        ]]);

        self::assertInstanceOf(UserfieldConfigEnumItemResult::class, $userfieldConfigItemResult->enum[0]);
        self::assertSame('First', $userfieldConfigItemResult->enum[0]->value);
        self::assertTrue($userfieldConfigItemResult->enum[0]->def);
        self::assertFalse($userfieldConfigItemResult->enum[1]->def);
    }

    public function testMissingAndEmptyEnumsRemainDistinct(): void
    {
        self::assertNull((new UserfieldConfigItemResult([]))->enum);
        self::assertNull((new UserfieldConfigItemResult(['enum' => null]))->enum);
        self::assertSame([], (new UserfieldConfigItemResult(['enum' => []]))->enum);
    }

    public function testEnumDefaultFlagIsBoolean(): void
    {
        self::assertTrue((new UserfieldConfigEnumItemResult(['def' => 'Y']))->def);
        self::assertFalse((new UserfieldConfigEnumItemResult(['def' => 'N']))->def);
    }
}
