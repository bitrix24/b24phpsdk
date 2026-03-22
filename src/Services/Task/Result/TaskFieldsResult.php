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

namespace Bitrix24\SDK\Services\Task\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class TaskFieldsResult extends AbstractResult
{
    /**
     * @return TaskFieldItemResult[]
     *
     * @throws BaseException
     */
    public function fields(): array
    {
        $fields = [];

        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $fieldData) {
            $fields[] = new TaskFieldItemResult($fieldData);
        }

        return $fields;
    }
}
