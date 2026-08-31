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

class DialogResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function dialog(): ?DialogItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        if (!array_is_list($result)) {
            return new DialogItemResult($result);
        }

        if (!is_array($result[0] ?? null)) {
            return null;
        }

        return new DialogItemResult($result[0]);
    }
}
