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
use Darsyn\IP\Version\Multi;
use Darsyn\IP\Exception\InvalidIpAddressException;
use PHPUnit\Framework\Attributes\DataProvider;

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
    #[DataProvider('ipAddresses')]
    public function testCastsIpAddresses(string $address): void
    {
        $annotatedIpItemStub = new AnnotatedIpItemStub(['address' => $address, 'text' => $address]);
        self::assertInstanceOf(Multi::class, $annotatedIpItemStub->address);
        self::assertSame($address, $annotatedIpItemStub->address->getProtocolAppropriateAddress());
        self::assertSame($address, $annotatedIpItemStub->text);
        self::assertSame($address, iterator_to_array($annotatedIpItemStub)['address']);
    }

    public static function ipAddresses(): iterable
    {
        yield ['192.0.2.1'];
        yield ['2001:db8::1'];
    }

    public function testNullableAndAlreadyTypedIpAddresses(): void
    {
        self::assertNull((new AnnotatedIpItemStub([]))->address);
        self::assertNull((new AnnotatedIpItemStub(['address' => null]))->address);
        self::assertNull((new AnnotatedIpItemStub(['address' => '']))->address);
        $address = Multi::factory('192.0.2.1');
        self::assertSame($address, (new AnnotatedIpItemStub(['address' => $address]))->address);
    }

    public function testRejectsInvalidIpAddress(): void
    {
        $this->expectException(InvalidIpAddressException::class);
        (new AnnotatedIpItemStub(['address' => 'invalid']))->address;
    }

    #[Test]
    public function testMagicGetterCastsNestedItemFromAnnotatedClass(): void
    {
        $item = new AnnotatedNestedItemStub(['interval' => ['from' => 3, 'title' => 'Integer settings']]);

        $this->assertInstanceOf(NestedIntervalItemStub::class, $item->interval);
        $this->assertSame(3, $item->interval->from);
        $this->assertSame('Integer settings', $item->interval->title);
        $this->assertSame($item->interval->from, (new AnnotatedNestedItemStub(['interval' => $item->interval]))->interval->from);
    }

    #[Test]
    public function testMagicGetterKeepsNullNestedItem(): void
    {
        $this->assertNull((new AnnotatedNestedItemStub(['interval' => null]))->interval);
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

/**
 * @property-read Multi|null $address
 * @property-read string $text
 */
final class AnnotatedIpItemStub extends AbstractAnnotatedItem
{
}

/**
 * @property-read NestedIntervalItemStub|null $interval
 */
final class AnnotatedNestedItemStub extends AbstractAnnotatedItem
{
}

/**
 * @property-read int|null    $from
 * @property-read string|null $title
 */
final class NestedIntervalItemStub extends AbstractAnnotatedItem
{
}
