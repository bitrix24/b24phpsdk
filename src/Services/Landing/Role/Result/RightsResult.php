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

class RightsResult extends AbstractResult
{
    /**
     * Get role rights for sites
     *
     * @return array Array where keys are site IDs and values are arrays of permissions
     * @throws BaseException
     */
    public function getRights(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return is_array($result) ? $result : [];
    }
}
