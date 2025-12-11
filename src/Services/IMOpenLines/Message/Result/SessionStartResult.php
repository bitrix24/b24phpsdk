<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines\Message\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SessionStartResult
 *
 * Result class for imopenlines.message.session.start
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Message\Result
 */
class SessionStartResult extends AbstractResult
{
    /**
     * Returns true when session was successfully started
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}