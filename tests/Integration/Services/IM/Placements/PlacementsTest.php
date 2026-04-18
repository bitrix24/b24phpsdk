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

use Bitrix24\SDK\Services\IM\Placements\ImContextMenuPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImNavigationPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImSidebarPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImTextareaPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementColor;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Placements::class)]
class PlacementsTest extends TestCase
{
    private ServiceBuilder $sb;

    #[Test]
    public function testTypedBindAndUnbindMethods(): void
    {
        $placements = $this->sb->getIMScope()->placements();

        $placements->unbindSidebar();
        $placements->unbindNavigation();
        $placements->unbindContextMenu();
        $placements->unbindTextarea();
        $placements->unbindSmilesSelector();

        self::assertTrue(
            $placements->bindSidebar(
                'https://bitrix24test.com/im-sidebar',
                [
                    'en' => ['TITLE' => 'Sidebar'],
                    'ru' => ['TITLE' => 'v']
                ],
                (new ImSidebarPlacementOptions('fa-bug'))
                    ->color(PlacementColor::Green),
            )->isSuccess()
        );

        self::assertTrue(
            $placements->bindNavigation(
                'https://bitrix24test.com/im-navigation',
                ['en' => ['TITLE' => 'Navigation']],
                new ImNavigationPlacementOptions('fa-compass'),
            )->isSuccess()
        );

        self::assertTrue(
            $placements->bindContextMenu(
                'https://bitrix24test.com/im-context-menu',
                ['en' => ['TITLE' => 'Context menu']],
                new ImContextMenuPlacementOptions(),
            )->isSuccess()
        );

        self::assertTrue(
            $placements->bindTextarea(
                'https://bitrix24test.com/im-textarea',
                ['en' => ['TITLE' => 'Textarea']],
                (new ImTextareaPlacementOptions('fa-comment'))
                    ->width(400)
                    ->height(160)
                    ->color(PlacementColor::Brown),
            )->isSuccess()
        );

        self::assertTrue(
            $placements->bindSmilesSelector(
                'https://bitrix24test.com/im-smiles-selector',
                ['en' => ['TITLE' => 'Smiles selector']],
                ['name' => 'fa-face-smile'],
            )->isSuccess()
        );

        self::assertGreaterThanOrEqual(0, $placements->unbindSidebar()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $placements->unbindNavigation()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $placements->unbindContextMenu()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $placements->unbindTextarea()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $placements->unbindSmilesSelector()->getDeletedPlacementHandlersCount());
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
    }
}
