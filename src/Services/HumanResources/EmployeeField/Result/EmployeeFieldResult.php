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

namespace Bitrix24\SDK\Services\HumanResources\EmployeeField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\HumanResources\Result\ResultExtractor;

class EmployeeFieldResult extends AbstractResult
{
    use ResultExtractor;

    /**
     * @throws BaseException
     */
    public function employeeField(): EmployeeFieldItemResult
    {
        return new EmployeeFieldItemResult($this->getItemData());
    }
}
