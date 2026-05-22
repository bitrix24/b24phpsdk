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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Attach;

use Bitrix24\SDK\Services\IM\Message\Attach\RawAttach;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RawAttach::class)]
final class RawAttachTest extends TestCase
{
    #[Test]
    public function buildReturnsRawShortAttachPayloadUnchanged(): void
    {
        $payload = [
            ['MESSAGE' => 'Raw message'],
            ['VENDOR_BLOCK' => ['FLAG' => 'Y']],
        ];

        self::assertSame(
            $payload,
            RawAttach::fromArray($payload)->build()
        );
    }

    #[Test]
    public function buildReturnsRawFullAttachPayloadUnchanged(): void
    {
        $payload = [
            'ID' => 1,
            'COLOR_TOKEN' => 'primary',
            'BLOCKS' => [
                ['MESSAGE' => 'Raw message'],
                ['VENDOR_BLOCK' => ['FLAG' => 'Y']],
            ],
        ];

        self::assertSame(
            $payload,
            RawAttach::fromArray($payload)->build()
        );
    }
}
