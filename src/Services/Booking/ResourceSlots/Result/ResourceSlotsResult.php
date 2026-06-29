<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\ResourceSlots\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ResourceSlotsResult extends AbstractResult
{
    /**
     * @return ResourceSlotItemResult[]
     * @throws BaseException
     */
    public function getSlots(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['slots'] as $item) {
            $items[] = new ResourceSlotItemResult($item);
        }

        return $items;
    }
}
