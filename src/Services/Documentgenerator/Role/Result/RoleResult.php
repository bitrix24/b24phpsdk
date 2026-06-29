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
 * Class RoleResult
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Role\Result
 */
class RoleResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function role(): RoleItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        if (!empty($result['role']) && is_array($result['role'])) {
            $result = $result['role'];
        }

        return new RoleItemResult($result);
    }
}
