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

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DeletedUserfieldConfigItemResult extends AbstractResult
{
    /**
     * userfieldconfig.delete returns a null result on success, unlike the boolean
     * `true` returned by most other *.delete methods.
     *
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        return $this->getCoreResponse()->getResponseData()->getResult()[0] === null;
    }
}
