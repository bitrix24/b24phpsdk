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

namespace Bitrix24\SDK\Tests\Unit\Application\Requests\Events;

use Bitrix24\SDK\Core\Requests\Events\EventRequestPayload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(EventRequestPayload::class)]
class EventRequestPayloadTest extends TestCase
{
    #[Test]
    public function testExtractPrefersParsedRequestPayload(): void
    {
        $request = new Request(
            [],
            [
                'event' => 'ONAPPINSTALL',
                'ts' => '1776544588',
            ],
            [],
            [],
            [],
            [],
            ''
        );

        self::assertSame(
            [
                'event' => 'ONAPPINSTALL',
                'ts' => '1776544588',
            ],
            EventRequestPayload::extract($request)
        );
    }

    #[Test]
    public function testExtractFallsBackToRawRequestBody(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            'event=ONAPPINSTALL&ts=1776544588'
        );

        self::assertSame(
            [
                'event' => 'ONAPPINSTALL',
                'ts' => '1776544588',
            ],
            EventRequestPayload::extract($request)
        );
    }
}
