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
 * Class ChatNameResult
 *
 * Result class for imconnector.chat.name.set method
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Connector\Result
 */
class ChatNameResult extends AbstractResult
{
    /**
     * Check if operation was successful
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
<<<<<<< HEAD

=======
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        // Response format: [SUCCESS] => 1 and [DATA] => Array(...)
        if (isset($result['SUCCESS'])) {
            return (bool)$result['SUCCESS'];
        }
<<<<<<< HEAD

        return false;
    }
}
=======
        
        return false;
    }
}
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
