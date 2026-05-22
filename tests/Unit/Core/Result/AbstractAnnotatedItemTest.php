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

namespace Bitrix24\SDK\Tests\Unit\Core\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractAnnotatedItem::class)]
final class AbstractAnnotatedItemTest extends TestCase
{
    #[Test]
    public function testMagicGetterCastsStringBackedEnumFromAnnotatedType(): void
    {
        $annotatedEnumItemStub = new AnnotatedEnumItemStub([
            'STATUS' => 'online',
        ]);

        self::assertSame(StringStatusStub::Online, $annotatedEnumItemStub->STATUS);
    }

    #[Test]
    public function testMagicGetterCastsIntBackedEnumFromAnnotatedType(): void
    {
        $annotatedEnumItemStub = new AnnotatedEnumItemStub([
            'CODE' => 1,
        ]);

        self::assertSame(IntStatusStub::Open, $annotatedEnumItemStub->CODE);
    }

    #[Test]
    public function testMagicGetterReturnsNullForEmptyNullableEnumValue(): void
    {
        $annotatedEnumItemStub = new AnnotatedEnumItemStub([
            'STATUS' => false,
        ]);

        self::assertNull($annotatedEnumItemStub->STATUS);
    }
}

/**
 * @property-read StringStatusStub|null $STATUS
 * @property-read IntStatusStub|null    $CODE
 */
final class AnnotatedEnumItemStub extends AbstractAnnotatedItem
{
}

enum StringStatusStub: string
{
    case Online = 'online';
}

enum IntStatusStub: int
{
    case Open = 1;
}
