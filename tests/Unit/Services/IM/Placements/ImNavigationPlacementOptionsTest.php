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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Placements;

use Bitrix24\SDK\Services\IM\Placements\ChatContext;
use Bitrix24\SDK\Services\IM\Placements\ImNavigationPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementColor;
use Bitrix24\SDK\Services\Placement\ExtranetAvailability;
use Bitrix24\SDK\Services\Placement\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImNavigationPlacementOptions::class)]
class ImNavigationPlacementOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('build() after constructor only contains the required iconName')]
    public function testBuildWithIconNameOnly(): void
    {
        $navigationPlacementOptions = new ImNavigationPlacementOptions('fa-compass');

        $this->assertSame(['iconName' => 'fa-compass'], $navigationPlacementOptions->build());
    }

    #[Test]
    #[TestDox('all fluent setters mutate build() output to expected payload')]
    public function testFullPayload(): void
    {
        $navigationPlacementOptions = (new ImNavigationPlacementOptions('fa-compass'))
            ->context(ChatContext::USER, ChatContext::CHAT)
            ->role(Role::Admin)
            ->color(PlacementColor::Aqua)
            ->width(280)
            ->height(160)
            ->extranet(ExtranetAvailability::Denied);

        $this->assertSame(
            [
                'iconName' => 'fa-compass',
                'context'  => 'USER;CHAT',
                'role'     => 'ADMIN',
                'color'    => 'AQUA',
                'width'    => 280,
                'height'   => 160,
                'extranet' => 'N',
            ],
            $navigationPlacementOptions->build(),
        );
    }
}
