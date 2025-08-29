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

namespace Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result;

use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

class PersonTypeStatusFieldsResult extends FieldsResult
{
    /**
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['businessValuePersonDomain'];
    }
}
