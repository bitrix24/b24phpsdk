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

namespace Bitrix24\SDK\Tests\Unit\Core\ValueObjects;
use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\{Url, LocalizedString, ValueObjectResolver};
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
#[CoversClass(ValueObjectResolver::class)]
class ValueObjectResolverTest extends TestCase
{
    public function testUrlFormsHaveIdenticalValue(): void
    {
        $url = 'https://example.com/callback?a=1';
        self::assertSame($url, ValueObjectResolver::resolveUrl($url));
        self::assertSame($url, ValueObjectResolver::resolveUrl(new Url($url)));
    }
    public function testInvalidUrlIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ValueObjectResolver::resolveUrl('not a URL');
    }
    public function testLocalizationFormsPreserveLanguagesAndEmptyMaps(): void
    {
        $value = (new LocalizedString(LangCodes::EN, 'Name'))->with(LangCodes::RU, 'Имя');
        self::assertSame(['en' => 'Name', 'ru' => 'Имя'], ValueObjectResolver::resolveLocalizedString($value));
        self::assertSame(['xx' => 'Legacy'], ValueObjectResolver::resolveLocalizedString(['xx' => 'Legacy']));
        self::assertSame([], ValueObjectResolver::resolveLocalizedString([]));
    }
}
