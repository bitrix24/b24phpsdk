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

namespace Bitrix24\SDK\Services\Sale\Status\Result;

use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;

/**
 * Class StatusUpdateBatchResult
 *
 * Result of the sale.status.update command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Result
 */
class StatusUpdateBatchResult extends UpdatedItemBatchResult
{
    #[\Override]
    public function isSuccess(): bool
    {
        return isset($this->getResponseData()->getResult()['status']);
    }

    public function status(): StatusItemResult
    {
        return new StatusItemResult($this->getResponseData()->getResult()['status']);
    }
}
