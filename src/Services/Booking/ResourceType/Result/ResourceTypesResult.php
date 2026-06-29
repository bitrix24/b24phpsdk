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

namespace Bitrix24\SDK\Services\Booking\ResourceType\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ResourceTypesResult extends AbstractResult
{
    /**
     * @return ResourceTypeItemResult[]
     * @throws BaseException
     */
    public function getResourceTypes(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['resourceType'] as $item) {
            $items[] = new ResourceTypeItemResult($item);
        }

        return $items;
    }
}
