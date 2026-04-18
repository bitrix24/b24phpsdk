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
        $service = $this->sb->getIMScope()->placements();

        $service->unbindSidebar();
        $service->unbindNavigation();
        $service->unbindContextMenu();
        $service->unbindTextarea();
        $service->unbindSmilesSelector();

        self::assertTrue(
            $service->bindSidebar(
                'https://bitrix24test.com/im-sidebar',
                ['en' => ['TITLE' => 'Sidebar']],
                (new ImSidebarPlacementOptions('fa-bug'))->color(PlacementColor::Green),
            )->isSuccess()
        );

        self::assertTrue(
            $service->bindNavigation(
                'https://bitrix24test.com/im-navigation',
                ['en' => ['TITLE' => 'Navigation']],
                new ImNavigationPlacementOptions('fa-compass'),
            )->isSuccess()
        );

        self::assertTrue(
            $service->bindContextMenu(
                'https://bitrix24test.com/im-context-menu',
                ['en' => ['TITLE' => 'Context menu']],
                new ImContextMenuPlacementOptions(),
            )->isSuccess()
        );

        self::assertTrue(
            $service->bindTextarea(
                'https://bitrix24test.com/im-textarea',
                ['en' => ['TITLE' => 'Textarea']],
                (new ImTextareaPlacementOptions('fa-comment'))->width(400)->height(160)->color(PlacementColor::Brown),
            )->isSuccess()
        );

        self::assertTrue(
            $service->bindSmilesSelector(
                'https://bitrix24test.com/im-smiles-selector',
                ['en' => ['TITLE' => 'Smiles selector']],
                ['name' => 'fa-face-smile'],
            )->isSuccess()
        );

        self::assertGreaterThanOrEqual(0, $service->unbindSidebar()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $service->unbindNavigation()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $service->unbindContextMenu()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $service->unbindTextarea()->getDeletedPlacementHandlersCount());
        self::assertGreaterThanOrEqual(0, $service->unbindSmilesSelector()->getDeletedPlacementHandlersCount());
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
    }
}
