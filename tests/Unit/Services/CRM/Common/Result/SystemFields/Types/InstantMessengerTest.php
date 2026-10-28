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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Common\Result\SystemFields\Types;

use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessengerValueType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ValueError;

#[CoversClass(InstantMessenger::class)]
final class InstantMessengerTest extends TestCase
{
    #[DataProvider('messengerValueTypes')]
    public function testSupportedValueTypeReturnsMessengerEnum(InstantMessengerValueType $expected): void
    {
        $instantMessenger = new InstantMessenger([
            'ID' => '7',
            'VALUE' => 'example',
            'VALUE_TYPE' => $expected->value,
        ]);

        self::assertSame($expected, $instantMessenger->VALUE_TYPE);
        self::assertSame(7, $instantMessenger->ID);
        self::assertSame('example', $instantMessenger->VALUE);
    }

    /**
     * @return iterable<string, array{InstantMessengerValueType}>
     */
    public static function messengerValueTypes(): iterable
    {
        foreach (InstantMessengerValueType::cases() as $valueType) {
            yield $valueType->value => [$valueType];
        }
    }

    public function testUnknownValueTypeThrowsValueError(): void
    {
        $instantMessenger = new InstantMessenger(['VALUE_TYPE' => 'UNKNOWN_MESSENGER']);

        $this->expectException(ValueError::class);

        $instantMessenger->__get('VALUE_TYPE');
    }

    public function testValueTypeAnnotationNamesMessengerEnum(): void
    {
        $docComment = (new ReflectionClass(InstantMessenger::class))->getDocComment();

        self::assertIsString($docComment);
        self::assertStringContainsString('@property-read InstantMessengerValueType $VALUE_TYPE', $docComment);
    }

    public function testOtherPropertiesPreserveParentBehavior(): void
    {
        $instantMessenger = new InstantMessenger(['CUSTOM_FIELD' => 'custom value']);

        self::assertSame('custom value', $instantMessenger->__get('CUSTOM_FIELD'));
        self::assertNull($instantMessenger->__get('MISSING_FIELD'));
    }
}
