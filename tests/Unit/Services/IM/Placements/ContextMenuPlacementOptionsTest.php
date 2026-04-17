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
use Bitrix24\SDK\Services\IM\Placements\ContextMenuPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ExtranetAvailability;
use Bitrix24\SDK\Services\IM\Placements\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContextMenuPlacementOptions::class)]
class ContextMenuPlacementOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('build() of a fresh instance returns an empty array')]
    public function testBuildIsEmptyByDefault(): void
    {
        $this->assertSame([], (new ContextMenuPlacementOptions())->build());
    }

    #[Test]
    #[TestDox('all fluent setters mutate build() output to expected payload')]
    public function testFullPayload(): void
    {
        $contextMenuPlacementOptions = (new ContextMenuPlacementOptions())
            ->context(ChatContext::User, ChatContext::Chat)
            ->role(Role::User)
            ->extranet(ExtranetAvailability::Denied);

        $this->assertSame(
            [
                'context'  => 'USER;CHAT',
                'role'     => 'USER',
                'extranet' => 'N',
            ],
            $contextMenuPlacementOptions->build(),
        );
    }

    #[Test]
    #[TestDox('fluent setters return $this and are chainable')]
    public function testFluentChainingReturnsSelf(): void
    {
        $contextMenuPlacementOptions = new ContextMenuPlacementOptions();

        $this->assertSame($contextMenuPlacementOptions, $contextMenuPlacementOptions->context(ChatContext::All));
        $this->assertSame($contextMenuPlacementOptions, $contextMenuPlacementOptions->role(Role::Admin));
        $this->assertSame($contextMenuPlacementOptions, $contextMenuPlacementOptions->extranet(ExtranetAvailability::Allowed));
    }
}
