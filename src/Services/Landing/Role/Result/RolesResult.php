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

namespace Bitrix24\SDK\Services\Landing\Role\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RolesResult extends AbstractResult
{
    /**
     * @return RoleItemResult[]
     * @throws BaseException
     */
    public function getRoles(): array
    {
        $res = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        foreach ($result as $item) {
            if (is_array($item)) {
                $res[] = new RoleItemResult($item);
            }
        }

        return $res;
    }
}
