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

namespace Bitrix24\SDK\Services\Messageservice\Sender\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class SenderUpdateResult extends AbstractResult
{
    /**
     * Check if sender was updated successfully.
     *
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
