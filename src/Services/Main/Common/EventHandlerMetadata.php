<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Bitrix24\SDK\Services\Main\Common;

use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Main\Result\EventHandlerItemResult;

/**
 * String constructor handler URLs are deprecated; pass Url instead. The public handlerUrl remains a string.
 * Legacy strings are preserved; Event::bind validates the effective handler after applying options.
 */
readonly class EventHandlerMetadata
{
    public string $handlerUrl;

    public function __construct(
        public string $code,
        string|Url $handlerUrl,
        public int    $userId,
        public ?array $options = null
    ) {
        $this->handlerUrl = $handlerUrl instanceof Url ? $handlerUrl->getUrl() : $handlerUrl;
    }

    public function isInstalled(EventHandlerItemResult $eventHandlerItemResult): bool
    {
        return strtoupper($eventHandlerItemResult->event) === strtoupper($this->code) &&
            $eventHandlerItemResult->handler === $this->handlerUrl;
    }
}
