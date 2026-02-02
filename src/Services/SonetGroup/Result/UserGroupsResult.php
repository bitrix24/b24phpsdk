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
 * Class UserGroupsResult
 *
 * @package Bitrix24\SDK\Services\SonetGroup\Result
 */
class UserGroupsResult extends AbstractResult
{
    /**
     * @return UserGroupItemResult[]
     * @throws BaseException
     */
    public function getUserGroups(): array
    {
        $groups = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        foreach ($result as $item) {
            $groups[] = new UserGroupItemResult($item);
        }

        return $groups;
    }
}
