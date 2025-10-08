<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Calendar\Event\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Users accessibility result
 */
class AccessibilityResult extends AbstractResult
{
    /**
     * Get user availability data
     * @return array<int, EventItemResult[]>
     */
    public function getUsersAccessibility(): array
    {
        $result = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $userId => $events) {
            $userEvents = [];
            foreach ($events as $event) {
                $userEvents[] = new EventItemResult($event);
            }

            $result[$userId] = $userEvents;
        }

        return $result;
    }
}
