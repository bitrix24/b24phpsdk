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

namespace Bitrix24\SDK\Services\IMOpenLines\Operator\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class OperatorActionResult
 *
 * Result of operator actions like answer, finish, skip, spam, transfer operations
 */
class OperatorActionResult extends AbstractResult
{
    /**
     * Check if operation was successful
     *
     * @throws BaseException
     */
    public function isSuccess(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
<<<<<<< HEAD

=======
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        // Handle different response formats
        if (array_key_exists(0, $result)) {
            $value = $result[0];
            if ($value === null) {
                return false;
            }

            return (bool)$value;
        }
<<<<<<< HEAD

        // For non-array results, convert to boolean
        return (bool)$result;
    }
}
=======
        
        // For non-array results, convert to boolean
        return (bool)$result;
    }
}
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
