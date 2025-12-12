<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\SonetGroup\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SonetGroupUserOperationResult
 * Handles results for SonetGroup user operations like add/delete user
 *
 * @package Bitrix24\SDK\Services\SonetGroup\Result
 */
class SonetGroupUserOperationResult extends AbstractResult
{
    /**
     * Check if operation was successful
     * SonetGroup user operations return empty array [] when successful
     * (e.g., when user is already a member or operation completed)
     *
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // For SonetGroup user operations, empty array means success
        if ($result === []) {
            return true;
        }

        // If result has elements, check first element for boolean value
        if (array_key_exists(0, $result)) {
            return (bool)$result[0];
        }

        // For scalar results
        return (bool)$result;
    }
}
