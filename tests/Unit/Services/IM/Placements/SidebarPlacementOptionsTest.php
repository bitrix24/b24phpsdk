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
use Bitrix24\SDK\Services\IM\Placements\ExtranetAvailability;
use Bitrix24\SDK\Services\IM\Placements\PlacementColor;
use Bitrix24\SDK\Services\IM\Placements\Role;
use Bitrix24\SDK\Services\IM\Placements\SidebarPlacementOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SidebarPlacementOptions::class)]
class SidebarPlacementOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('build() after constructor only contains the required iconName')]
    public function testBuildWithIconNameOnly(): void
    {
        $sidebarPlacementOptions = new SidebarPlacementOptions('fa-bug');

        $this->assertSame(['iconName' => 'fa-bug'], $sidebarPlacementOptions->build());
    }

    #[Test]
    #[TestDox('all fluent setters mutate build() output to expected payload')]
    public function testFullPayload(): void
    {
        $sidebarPlacementOptions = (new SidebarPlacementOptions('fa-bug'))
            ->context(ChatContext::User, ChatContext::Lines)
            ->role(Role::Admin)
            ->color(PlacementColor::Aqua)
            ->extranet(ExtranetAvailability::Denied);

        $this->assertSame(
            [
                'iconName' => 'fa-bug',
                'context'  => 'USER;LINES',
                'role'     => 'ADMIN',
                'color'    => 'AQUA',
                'extranet' => 'N',
            ],
            $sidebarPlacementOptions->build(),
        );
    }

    #[Test]
    #[TestDox('fluent setters return $this and are chainable')]
    public function testFluentChainingReturnsSelf(): void
    {
        $sidebarPlacementOptions = new SidebarPlacementOptions('fa-bug');

        $this->assertSame($sidebarPlacementOptions, $sidebarPlacementOptions->context(ChatContext::Crm));
        $this->assertSame($sidebarPlacementOptions, $sidebarPlacementOptions->role(Role::User));
        $this->assertSame($sidebarPlacementOptions, $sidebarPlacementOptions->extranet(ExtranetAvailability::Allowed));
        $this->assertSame($sidebarPlacementOptions, $sidebarPlacementOptions->color(PlacementColor::Pink));
    }
}
