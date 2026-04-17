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

namespace Bitrix24\SDK\Services\IM;

/**
 * IM widget placement codes (scope `im`).
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/im/index.html
 */
class PlacementLocationCodes
{
    // Widget panel above the chat message input field.
    // See https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html
    public const IM_TEXTAREA = 'IM_TEXTAREA';

    // Chat sidebar widget.
    // See https://apidocs.bitrix24.com/api-reference/widgets/im/sidebar.html
    public const IM_SIDEBAR = 'IM_SIDEBAR';

    // Context-menu item on a chat message ("Create content based on").
    // See https://apidocs.bitrix24.com/api-reference/widgets/im/context-menu.html
    public const IM_CONTEXT_MENU = 'IM_CONTEXT_MENU';

    /**
     * Smiles / Giphy selector pop-up.
     *
     * @deprecated No longer works since module `im 25.1600.0` — smiles were
     *             replaced by stickers. See
     *             https://apidocs.bitrix24.com/api-reference/widgets/im/smile-selector.html
     */
    public const IM_SMILES_SELECTOR = 'IM_SMILES_SELECTOR';
}
