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

namespace Bitrix24\SDK\Services\SonetGroup\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SonetGroupsResult
 *
 * @package Bitrix24\SDK\Services\SonetGroup\Result
 */
class SonetGroupsResult extends AbstractResult
{
    /**
     * @return SonetGroupItemResult[]
     * @throws BaseException
     */
    public function getGroups(): array
    {
        $groups = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // Handle different response formats for socialnetwork.api.workgroup.list
        $items = [];
        if (isset($result['workgroups']) && is_array($result['workgroups'])) {
            // For socialnetwork.api.workgroup.list
            $items = $result['workgroups'];
        } elseif (is_array($result) && !isset($result['workgroups'])) {
            // For sonet_group.get
            $items = $result;
        }

        foreach ($items as $item) {
            $groups[] = new SonetGroupItemResult($item);
        }

        return $groups;
    }
}
