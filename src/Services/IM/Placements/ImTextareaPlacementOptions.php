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

/**
 * Fluent options builder for the `IM_TEXTAREA` placement
 * (widget panel above the chat message input field).
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/im/textarea.html
 */
class ImTextareaPlacementOptions extends AbstractPlacementOptions
{
    public function __construct(string $iconName)
    {
        $this->fields['iconName'] = $iconName;
    }

    public function color(PlacementColor $placementColor): self
    {
        $this->fields['color'] = $placementColor->value;

        return $this;
    }

    public function width(int $width): self
    {
        $this->fields['width'] = $width;

        return $this;
    }

    public function height(int $height): self
    {
        $this->fields['height'] = $height;

        return $this;
    }
}
