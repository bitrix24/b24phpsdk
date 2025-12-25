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
 * Class SendMessagesResult
 *
 * Result class for imconnector.send.messages, imconnector.update.messages, imconnector.delete.messages methods
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Connector\Result
 */
class SendMessagesResult extends AbstractResult
{
    /**
     * Check if operation was successful
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // Response format: [SUCCESS] => 1
        if (isset($result['SUCCESS'])) {
            return (bool)$result['SUCCESS'];
        }
        
        return false;
    }
    
    /**
     * Get operation result data
     *
     * @return array{
     *   SUCCESS?: int,
     *   DATA?: array
     * }
     */
    public function getResult(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
    
    /**
     * Get result data
     */
    public function getData(): ?array
    {
        $result = $this->getResult();
        
        return $result['DATA'] ?? null;
    }
}