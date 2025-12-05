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
     *
     * @return array{
     *   LINE: int,
     *   CONNECTOR: string,
     *   ERROR: bool|string,
     *   CONFIGURED: bool|string,
     *   STATUS: bool|string
     * }
     */
    public function getStatus(): array
    {
        // Response format: [LINE], [CONNECTOR], [ERROR], [CONFIGURED], [STATUS]
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}