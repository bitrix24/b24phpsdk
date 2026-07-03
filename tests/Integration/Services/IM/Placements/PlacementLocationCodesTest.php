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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Placements;

use Bitrix24\SDK\Services\IM\Placements\ContextMenuPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementColor;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\IM\Placements\SidebarPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\TextareaPlacementOptions;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(PlacementLocationCodes::class)]
class PlacementLocationCodesTest extends TestCase
{
    private ServiceBuilder $sb;

    #[Test]
    #[TestDox('PlacementLocationCodes class declares every IM placement code returned by placement.list')]
    public function testAllApiImPlacementsAreDeclared(): void
    {
        $remoteCodes = $this->sb->getPlacementScope()->placement()->list()->getLocationCodes();

        $remoteImCodes = array_values(array_filter(
            $remoteCodes,
            static fn (string $code): bool => str_starts_with($code, 'IM_'),
        ));

        $reflectionClass = new ReflectionClass(PlacementLocationCodes::class);
        $declaredCodes = array_values($reflectionClass->getConstants());

        $missing = array_values(array_diff($remoteImCodes, $declaredCodes));

        $this->assertSame(
            [],
            $missing,
            sprintf(
                'PlacementLocationCodes is missing constants for IM placements returned by placement.list: %s',
                implode(', ', $missing),
            ),
        );
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
    }
}
