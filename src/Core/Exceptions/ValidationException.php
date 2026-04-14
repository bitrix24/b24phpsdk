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

use Bitrix24\SDK\Core\Response\DTO\ValidationError;

class ValidationException extends BaseException
{
    /**
     * @param ValidationError[] $validationErrors
     */
    public function __construct(
        string $message = '',
        private readonly array $validationErrors = [],
        int $code = 0,
        ?\Throwable $throwable = null,
    ) {
        parent::__construct($message, $code, $throwable);
    }

    /**
     * @return ValidationError[]
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
