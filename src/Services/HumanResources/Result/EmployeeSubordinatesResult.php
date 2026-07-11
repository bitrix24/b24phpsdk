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

namespace Bitrix24\SDK\Services\HumanResources\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class EmployeeSubordinatesResult extends AbstractResult
{
    use ResultExtractor;

    /**
     * @throws BaseException
     */
    public function getUserId(): int
    {
        return (int)($this->getResultData()['userId'] ?? 0);
    }

    /**
     * @return int[]
     * @throws BaseException
     */
    public function getDepartments(): array
    {
        return $this->getResultData()['departments'] ?? [];
    }
}
