<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Legacy\Services\Task\Result;

use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class TaskFieldsResult
 *
 * @package Bitrix24\SDK\Legacy\Services\Task\Result
 */
class TaskFieldsResult extends FieldsResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['fields'];
    }
}
