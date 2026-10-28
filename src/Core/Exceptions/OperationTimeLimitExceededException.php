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

namespace Bitrix24\SDK\Core\Exceptions;

use Throwable;

/**
 * Method is blocked because its operating time exceeded the limit
 *
 * @see https://apidocs.bitrix24.com/limits.html
 */
class OperationTimeLimitExceededException extends BaseException
{
    /**
     * @param string|null $apiMethod blocked api method, if known
     * @param string|null $batchCommandId command id if the error came from a batch request
     * @param int|null $operatingResetAt unix timestamp from the last successful response of this method, if seen
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $throwable = null,
        public readonly ?string $apiMethod = null,
        public readonly ?string $batchCommandId = null,
        public readonly ?int $operatingResetAt = null
    ) {
        parent::__construct($message, $code, $throwable);
    }
}
