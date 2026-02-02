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
 * Class QuickSaveResult
 *
 * Result class for imopenlines.message.quick.save
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Message\Result
 */
class QuickSaveResult extends AbstractResult
{
    /**
     * Returns true when successfully saved to quick answers
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}