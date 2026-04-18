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

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Services\IM\Placements\ImContextMenuPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImNavigationPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImSidebarPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\ImTextareaPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangItem;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangMap;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\Placement\Result\PlacementBindResult;
use Bitrix24\SDK\Services\Placement\Service\Placement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Placements::class)]
class PlacementsTest extends TestCase
{
    #[Test]
    public function testBindSidebarBuildsLangPayloadInternally(): void
    {
        $placementService = $this->createMock(Placement::class);
        $bindResult = $this->createStub(PlacementBindResult::class);
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('Sidebar', 'Description', 'Group'));
        $imSidebarPlacementOptions = new ImSidebarPlacementOptions('fa-bug');

        $placementService->expects($this->once())
            ->method('bind')
            ->with(
                PlacementLocationCodes::IM_SIDEBAR,
                'https://example.test/sidebar',
                [
                    'en' => [
                        'TITLE' => 'Sidebar',
                        'DESCRIPTION' => 'Description',
                        'GROUP_NAME' => 'Group',
                    ],
                ],
                $imSidebarPlacementOptions,
                7,
            )
            ->willReturn($bindResult);

        $this->assertSame(
            $bindResult,
            (new Placements($placementService))->bindSidebar(
                'https://example.test/sidebar',
                $placementLangMap,
                $imSidebarPlacementOptions,
                7,
            ),
        );
    }

    #[Test]
    public function testBindNavigationBuildsLangPayloadInternally(): void
    {
        $placementService = $this->createMock(Placement::class);
        $bindResult = $this->createStub(PlacementBindResult::class);
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('Navigation'));
        $imNavigationPlacementOptions = new ImNavigationPlacementOptions('fa-compass');

        $placementService->expects($this->once())
            ->method('bind')
            ->with(
                PlacementLocationCodes::IM_NAVIGATION,
                'https://example.test/navigation',
                [
                    'en' => [
                        'TITLE' => 'Navigation',
                    ],
                ],
                $imNavigationPlacementOptions,
                null,
            )
            ->willReturn($bindResult);

        $this->assertSame(
            $bindResult,
            (new Placements($placementService))->bindNavigation(
                'https://example.test/navigation',
                $placementLangMap,
                $imNavigationPlacementOptions,
            ),
        );
    }

    #[Test]
    public function testBindContextMenuBuildsLangPayloadInternally(): void
    {
        $placementService = $this->createMock(Placement::class);
        $bindResult = $this->createStub(PlacementBindResult::class);
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('Context menu'));
        $imContextMenuPlacementOptions = new ImContextMenuPlacementOptions();

        $placementService->expects($this->once())
            ->method('bind')
            ->with(
                PlacementLocationCodes::IM_CONTEXT_MENU,
                'https://example.test/context-menu',
                [
                    'en' => [
                        'TITLE' => 'Context menu',
                    ],
                ],
                $imContextMenuPlacementOptions,
                null,
            )
            ->willReturn($bindResult);

        $this->assertSame(
            $bindResult,
            (new Placements($placementService))->bindContextMenu(
                'https://example.test/context-menu',
                $placementLangMap,
                $imContextMenuPlacementOptions,
            ),
        );
    }

    #[Test]
    public function testBindTextareaBuildsLangPayloadInternally(): void
    {
        $placementService = $this->createMock(Placement::class);
        $bindResult = $this->createStub(PlacementBindResult::class);
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('Textarea'));
        $imTextareaPlacementOptions = new ImTextareaPlacementOptions('fa-comment');

        $placementService->expects($this->once())
            ->method('bind')
            ->with(
                PlacementLocationCodes::IM_TEXTAREA,
                'https://example.test/textarea',
                [
                    'en' => [
                        'TITLE' => 'Textarea',
                    ],
                ],
                $imTextareaPlacementOptions,
                null,
            )
            ->willReturn($bindResult);

        $this->assertSame(
            $bindResult,
            (new Placements($placementService))->bindTextarea(
                'https://example.test/textarea',
                $placementLangMap,
                $imTextareaPlacementOptions,
            ),
        );
    }

    #[Test]
    public function testBindSmilesSelectorBuildsLangPayloadInternally(): void
    {
        $placementService = $this->createMock(Placement::class);
        $bindResult = $this->createStub(PlacementBindResult::class);
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('Smiles selector'));

        $placementService->expects($this->once())
            ->method('bind')
            ->with(
                PlacementLocationCodes::IM_SMILES_SELECTOR,
                'https://example.test/smiles-selector',
                [
                    'en' => [
                        'TITLE' => 'Smiles selector',
                    ],
                ],
                ['name' => 'fa-face-smile'],
                null,
            )
            ->willReturn($bindResult);

        $this->assertSame(
            $bindResult,
            (new Placements($placementService))->bindSmilesSelector(
                'https://example.test/smiles-selector',
                $placementLangMap,
                ['name' => 'fa-face-smile'],
            ),
        );
    }
}
