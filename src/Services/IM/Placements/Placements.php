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
final class Placements
{
    public function __construct(private readonly Placement $placementService)
    {
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function bindSidebar(
        string $handlerUrl,
        array $lang,
        ImSidebarPlacementOptions $options,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_SIDEBAR,
            $handlerUrl,
            $lang,
            $options,
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
        ImNavigationPlacementOptions $options,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_NAVIGATION,
            $handlerUrl,
            $lang,
            $options,
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
        ImContextMenuPlacementOptions $options,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_CONTEXT_MENU,
            $handlerUrl,
            $lang,
            $options,
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
        ImTextareaPlacementOptions $options,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_TEXTAREA,
            $handlerUrl,
            $lang,
            $options,
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
        array $options = [],
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_SMILES_SELECTOR,
            $handlerUrl,
            $lang,
            $options,
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
     * @throws BaseException
     * @throws TransportException
     */
    public function unbindSmilesSelector(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_SMILES_SELECTOR, $handlerUrl);
    }
}
