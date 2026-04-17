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
use Bitrix24\SDK\Services\IM\Placements\TextareaPlacementOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextareaPlacementOptions::class)]
class TextareaPlacementOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('build() after constructor only contains the required iconName')]
    public function testBuildWithIconNameOnly(): void
    {
        $textareaPlacementOptions = new TextareaPlacementOptions('fa-bars');

        $this->assertSame(['iconName' => 'fa-bars'], $textareaPlacementOptions->build());
    }

    #[Test]
    #[TestDox('all fluent setters mutate build() output to expected payload')]
    public function testFullPayload(): void
    {
        $textareaPlacementOptions = (new TextareaPlacementOptions('fa-bars'))
            ->context(ChatContext::User, ChatContext::Chat)
            ->role(Role::User)
            ->color(PlacementColor::Graphite)
            ->width(200)
            ->height(100)
            ->extranet(ExtranetAvailability::Denied);

        $this->assertSame(
            [
                'iconName' => 'fa-bars',
                'context'  => 'USER;CHAT',
                'role'     => 'USER',
                'color'    => 'GRAPHITE',
                'width'    => 200,
                'height'   => 100,
                'extranet' => 'N',
            ],
            $textareaPlacementOptions->build(),
        );
    }

    #[Test]
    #[TestDox('context() with single case produces a value without separators')]
    public function testContextSingleCase(): void
    {
        $textareaPlacementOptions = (new TextareaPlacementOptions('fa-bars'))->context(ChatContext::All);

        $this->assertSame('ALL', $textareaPlacementOptions->build()['context']);
    }

    #[Test]
    #[TestDox('fluent setters return $this and are chainable')]
    public function testFluentChainingReturnsSelf(): void
    {
        $textareaPlacementOptions = new TextareaPlacementOptions('fa-bars');

        $this->assertSame($textareaPlacementOptions, $textareaPlacementOptions->context(ChatContext::All));
        $this->assertSame($textareaPlacementOptions, $textareaPlacementOptions->role(Role::Admin));
        $this->assertSame($textareaPlacementOptions, $textareaPlacementOptions->extranet(ExtranetAvailability::Allowed));
        $this->assertSame($textareaPlacementOptions, $textareaPlacementOptions->color(PlacementColor::Aqua));
        $this->assertSame($textareaPlacementOptions, $textareaPlacementOptions->width(150));
        $this->assertSame($textareaPlacementOptions, $textareaPlacementOptions->height(120));
    }
}
