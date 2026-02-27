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
 * Class SonetGetGroupsResult
 *
 * @package Bitrix24\SDK\Services\SonetGroup\Result
 */
class SonetGetGroupsResult extends AbstractResult
{
    /**
     * Returns groups for socialnetwork.api.workgroup.list method
     *
     * @return SonetGroupGetItemResult[]
     * @throws BaseException
     */
    public function getGroups(): array
    {
        $groups = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!isset($result['workgroups'])) {
            // For sonet_group.get
            foreach ($result as $item) {
                $groups[] = new SonetGroupGetItemResult($item);
            }

        }

        return $groups;
    }
}
