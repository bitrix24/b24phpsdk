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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SendersListResult extends AbstractResult
{
    /**
     * Get list of registered sender codes.
     *
     * @return string[]
     * @throws BaseException
     */
    public function getSenderCodes(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return is_array($result) ? array_values(array_map('strval', $result)) : [];
    }
}
