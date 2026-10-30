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

namespace Bitrix24\SDK\Tests\Unit\Services\Placement\Common;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;
use Bitrix24\SDK\Services\Placement\PlacementOptionsInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlacementHandlerMetadataTest extends TestCase
{
    public function testNormalizesCodeAndSnapshotsOptionsWithoutChangingUrl(): void
    {
        $options = new class () implements PlacementOptionsInterface {
            public string $value = 'original';
            public function build(): array
            {
                return ['value' => $this->value];
            }
        };
        $url = new Url('https://example.com/Handler?a=1&B=2');
        $metadata = new PlacementHandlerMetadata('crm_contact_detail_tab', $url, ['en' => ['TITLE' => 'Tab']], $options, 8);
        $options->value = 'changed';
        self::assertSame('CRM_CONTACT_DETAIL_TAB', $metadata->placementCode);
        self::assertSame($url, $metadata->handlerUrl);
        self::assertSame(['value' => 'original'], $metadata->options);
        self::assertSame(['en' => ['TITLE' => 'Tab']], $metadata->lang);
        self::assertSame(8, $metadata->b24UserId);
    }

    public function testDefaultsAndReadonlyProperties(): void
    {
        $metadata = new PlacementHandlerMetadata('CODE', 'https://example.com/');
        self::assertSame([], $metadata->lang);
        self::assertSame([], $metadata->options);
        self::assertNull($metadata->b24UserId);
        $this->expectException(\Error::class);
        (new \ReflectionProperty($metadata, 'options'))->setValue($metadata, ['x' => 1]);
    }

    public static function invalidDeclarations(): array
    {
        return [
            ['', 'https://example.com/', null], ['   ', 'https://example.com/', null],
            ['CODE', '', null], ['CODE', 'ftp://example.com/file', null],
            ['CODE', 'relative/path', null], ['CODE', 'https://example.com/', 0],
            ['CODE', 'https://example.com/', -1],
        ];
    }

    #[DataProvider('invalidDeclarations')]
    public function testRejectsInvalidDeclaration(string $code, string $url, ?int $userId): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PlacementHandlerMetadata($code, $url, b24UserId: $userId);
    }
}
