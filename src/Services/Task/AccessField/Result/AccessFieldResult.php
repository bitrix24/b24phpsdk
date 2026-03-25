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

namespace Bitrix24\SDK\Services\Task\AccessField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class AccessFieldResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function accessField(): AccessFieldItemResult
    {
        return new AccessFieldItemResult(
            $this->getCoreResponse()->getResponseData()->getResult()['item']
        );
    }
}
