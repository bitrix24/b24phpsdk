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

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogReadResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function readState(): ?DialogReadStateItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        $payload = array_is_list($result) && is_array($result[0] ?? null) ? $result[0] : $result;

        if ($payload === []) {
            return null;
        }

        return new DialogReadStateItemResult($payload);
    }
}
