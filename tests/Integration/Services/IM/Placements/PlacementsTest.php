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

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Services\IM\Placements\ImContextMenuPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImNavigationPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImSidebarPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImTextareaPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementColor;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangItem;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangMap;
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

        $suffix = '/sdk533-' . bin2hex(random_bytes(6));
        $base = rtrim((string) $_ENV['BITRIX24_PHP_SDK_APPLICATION_DOMAIN_URL'], '/');
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('Sidebar'))
            ->with(LangCodes::RU, new PlacementLangItem('Сайдбар'));

        try {
            self::assertTrue(
                $placements->bindSidebar(
                    new \Bitrix24\SDK\Core\ValueObjects\Url($base . '/im-sidebar' . $suffix),
                    $placementLangMap,
                    (new ImSidebarPlacementOptions('fa-bug'))
                        ->color(PlacementColor::Green),
                )->isSuccess()
            );

            self::assertTrue(
                $placements->bindNavigation(
                    $base . '/im-navigation' . $suffix,
                    PlacementLangMap::empty()
                        ->with(LangCodes::EN, new PlacementLangItem('Navigation')),
                    new ImNavigationPlacementOptions('fa-compass'),
                )->isSuccess()
            );

            self::assertTrue(
                $placements->bindContextMenu(
                    $base . '/im-context-menu' . $suffix,
                    PlacementLangMap::empty()
                        ->with(LangCodes::EN, new PlacementLangItem('Context menu')),
                    new ImContextMenuPlacementOptions(),
                )->isSuccess()
            );

            self::assertTrue(
                $placements->bindTextarea(
                    $base . '/im-textarea' . $suffix,
                    PlacementLangMap::empty()
                        ->with(LangCodes::EN, new PlacementLangItem('Textarea')),
                    (new ImTextareaPlacementOptions('fa-comment'))
                        ->width(400)
                        ->height(160)
                        ->color(PlacementColor::Brown),
                )->isSuccess()
            );

            self::assertTrue(
                $placements->bindSmilesSelector(
                    $base . '/im-smiles-selector' . $suffix,
                    PlacementLangMap::empty()
                        ->with(LangCodes::EN, new PlacementLangItem('Smiles selector')),
                    ['name' => 'fa-face-smile'],
                )->isSuccess()
            );

        } finally {
            self::assertGreaterThanOrEqual(0, $placements->unbindSidebar(new \Bitrix24\SDK\Core\ValueObjects\Url($base . '/im-sidebar' . $suffix))->getDeletedPlacementHandlersCount());
            self::assertGreaterThanOrEqual(0, $placements->unbindNavigation(new \Bitrix24\SDK\Core\ValueObjects\Url($base . '/im-navigation' . $suffix))->getDeletedPlacementHandlersCount());
            self::assertGreaterThanOrEqual(0, $placements->unbindContextMenu(new \Bitrix24\SDK\Core\ValueObjects\Url($base . '/im-context-menu' . $suffix))->getDeletedPlacementHandlersCount());
            self::assertGreaterThanOrEqual(0, $placements->unbindTextarea(new \Bitrix24\SDK\Core\ValueObjects\Url($base . '/im-textarea' . $suffix))->getDeletedPlacementHandlersCount());
            self::assertGreaterThanOrEqual(0, $placements->unbindSmilesSelector(new \Bitrix24\SDK\Core\ValueObjects\Url($base . '/im-smiles-selector' . $suffix))->getDeletedPlacementHandlersCount());
        }
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
    }
}
