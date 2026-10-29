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

namespace Bitrix24\SDK\Tests\Unit\Services\Placement\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Placement\PlacementServiceBuilder;
use Bitrix24\SDK\Services\Placement\Service\Placement;
use Bitrix24\SDK\Services\Placement\Service\PlacementRegistrar;
use Bitrix24\SDK\Tests\Unit\Services\Placement\ResponseFixture;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class PlacementTest extends TestCase
{
    use ResponseFixture;

    public static function unbindPayloads(): array
    {
        return [
            [null, null, ['PLACEMENT' => 'CODE', 'HANDLER' => null]],
            ['', null, ['PLACEMENT' => 'CODE', 'HANDLER' => '']],
            ['https://example.com/', null, ['PLACEMENT' => 'CODE', 'HANDLER' => 'https://example.com/']],
            [new Url('https://example.com/'), 0, ['PLACEMENT' => 'CODE', 'HANDLER' => 'https://example.com/', 'USER_ID' => 0]],
            ['https://example.com/', 8, ['PLACEMENT' => 'CODE', 'HANDLER' => 'https://example.com/', 'USER_ID' => 8]],
        ];
    }

    #[DataProvider('unbindPayloads')]
    public function testUnbindPreservesLegacyPayloadAndSupportsExactUser(string|Url|null $url, ?int $userId, array $payload): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('placement.unbind', $payload)->willReturn($this->response(['count' => 1], 'placement.unbind'));
        self::assertSame(1, (new Placement($core, new NullLogger()))->unbind('CODE', $url, $userId)->getDeletedPlacementHandlersCount());
    }

    public function testBuilderCachesRegistrar(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $builder = new PlacementServiceBuilder($core, new NullBatch(), new NullBulkItemsReader(), new NullLogger());
        self::assertInstanceOf(PlacementRegistrar::class, $builder->registrar());
        self::assertSame($builder->registrar(), $builder->registrar());
        self::assertSame($builder->placement(), $builder->placement());
        self::assertTrue($builder->registrar()->register([])->isSuccess());
    }

    public function testLegacyBindPayloadAndBooleanResultRemainUnchanged(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('placement.bind', [
            'PLACEMENT' => 'CODE', 'HANDLER' => 'https://example.com/', 'LANG_ALL' => ['en' => ['TITLE' => 'Tab']], 'OPTIONS' => [], 'USER_ID' => null,
        ])->willReturn($this->response(true, 'placement.bind'));
        self::assertTrue((new Placement($core, new NullLogger()))->bind('CODE', 'https://example.com/', ['en' => ['TITLE' => 'Tab']])->isSuccess());
    }

}
