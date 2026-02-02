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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class StatusResult
 *
 * Result class for imconnector.status method
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Connector\Result
 */
class StatusResult extends AbstractResult
{
    /**
     * Get connector status information
     */
    public function getStatus(): StatusItemResult
    {
        return new StatusItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
