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

namespace Bitrix24\SDK\Services\IM\Placements;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Placement\Result\PlacementBindResult;
use Bitrix24\SDK\Services\Placement\Result\PlacementUnbindResult;
use Bitrix24\SDK\Services\Placement\Service\Placement;

/**
 * Typed placement registration helpers for IM widgets.
 */
final readonly class Placements
{
    public function __construct(private Placement $placementService)
    {
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function bindSidebar(
        string $handlerUrl,
        array $lang,
        ImSidebarPlacementOptions $imSidebarPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_SIDEBAR,
            $handlerUrl,
            $lang,
            $imSidebarPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function bindNavigation(
        string $handlerUrl,
        array $lang,
        ImNavigationPlacementOptions $imNavigationPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_NAVIGATION,
            $handlerUrl,
            $lang,
            $imNavigationPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function bindContextMenu(
        string $handlerUrl,
        array $lang,
        ImContextMenuPlacementOptions $imContextMenuPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_CONTEXT_MENU,
            $handlerUrl,
            $lang,
            $imContextMenuPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function bindTextarea(
        string $handlerUrl,
        array $lang,
        ImTextareaPlacementOptions $imTextareaPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_TEXTAREA,
            $handlerUrl,
            $lang,
            $imTextareaPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     * @deprecated
     */
    public function bindSmilesSelector(
        string $handlerUrl,
        array $lang,
        array $imSmilesSelectorPlacementOptions = [],
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_SMILES_SELECTOR,
            $handlerUrl,
            $lang,
            $imSmilesSelectorPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function unbindSidebar(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_SIDEBAR, $handlerUrl);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function unbindNavigation(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_NAVIGATION, $handlerUrl);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function unbindContextMenu(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_CONTEXT_MENU, $handlerUrl);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function unbindTextarea(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_TEXTAREA, $handlerUrl);
    }

    /**
     * @deprecated
     * @throws BaseException
     * @throws TransportException
     */
    public function unbindSmilesSelector(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_SMILES_SELECTOR, $handlerUrl);
    }
}
