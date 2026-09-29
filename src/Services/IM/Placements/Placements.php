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
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/im/index.html
 */
final readonly class Placements
{
    public function __construct(private Placement $placementService)
    {
    }

    /**
     * Register the `IM_SIDEBAR` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/sidebar.html
     */
    public function bindSidebar(
        string $handlerUrl,
        PlacementLangMap $placementLangMap,
        ImSidebarPlacementOptions $imSidebarPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_SIDEBAR,
            $handlerUrl,
            $placementLangMap->toArray(),
            $imSidebarPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * Register the `IM_NAVIGATION` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/index.html
     */
    public function bindNavigation(
        string $handlerUrl,
        PlacementLangMap $placementLangMap,
        ImNavigationPlacementOptions $imNavigationPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_NAVIGATION,
            $handlerUrl,
            $placementLangMap->toArray(),
            $imNavigationPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * Register the `IM_CONTEXT_MENU` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/context-menu.html
     */
    public function bindContextMenu(
        string $handlerUrl,
        PlacementLangMap $placementLangMap,
        ImContextMenuPlacementOptions $imContextMenuPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_CONTEXT_MENU,
            $handlerUrl,
            $placementLangMap->toArray(),
            $imContextMenuPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * Register the `IM_TEXTAREA` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html
     */
    public function bindTextarea(
        string $handlerUrl,
        PlacementLangMap $placementLangMap,
        ImTextareaPlacementOptions $imTextareaPlacementOptions,
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_TEXTAREA,
            $handlerUrl,
            $placementLangMap->toArray(),
            $imTextareaPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * Register the deprecated `IM_SMILES_SELECTOR` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @deprecated
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/smile-selector.html
     */
    public function bindSmilesSelector(
        string $handlerUrl,
        PlacementLangMap $placementLangMap,
        array $imSmilesSelectorPlacementOptions = [],
        ?int $b24UserId = null,
    ): PlacementBindResult {
        return $this->placementService->bind(
            PlacementLocationCodes::IM_SMILES_SELECTOR,
            $handlerUrl,
            $placementLangMap->toArray(),
            $imSmilesSelectorPlacementOptions,
            $b24UserId,
        );
    }

    /**
     * Unregister the `IM_SIDEBAR` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/sidebar.html
     */
    public function unbindSidebar(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_SIDEBAR, $handlerUrl);
    }

    /**
     * Unregister the `IM_NAVIGATION` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/index.html
     */
    public function unbindNavigation(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_NAVIGATION, $handlerUrl);
    }

    /**
     * Unregister the `IM_CONTEXT_MENU` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/context-menu.html
     */
    public function unbindContextMenu(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_CONTEXT_MENU, $handlerUrl);
    }

    /**
     * Unregister the `IM_TEXTAREA` placement handler.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html
     */
    public function unbindTextarea(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_TEXTAREA, $handlerUrl);
    }

    /**
     * Unregister the deprecated `IM_SMILES_SELECTOR` placement handler.
     *
     * @deprecated
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/widgets/im/smile-selector.html
     */
    public function unbindSmilesSelector(?string $handlerUrl = null): PlacementUnbindResult
    {
        return $this->placementService->unbind(PlacementLocationCodes::IM_SMILES_SELECTOR, $handlerUrl);
    }
}
