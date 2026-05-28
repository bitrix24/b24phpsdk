<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Documentgenerator\Role\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class RolesResult
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Role\Result
 */
class RolesResult extends AbstractResult
{
    /**
     * @return RoleItemResult[]
     * @throws BaseException
     */
    public function getRoles(): array
    {
        $items = [];
        $source = [];

        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['roles']) && is_array($result['roles'])) {
            $source = $result['roles'];
        }

        foreach ($source as $item) {
            $items[] = new RoleItemResult($item);
        }

        return $items;
    }
}
