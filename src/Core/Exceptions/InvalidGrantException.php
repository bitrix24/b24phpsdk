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

/**
 * Exception thrown when OAuth refresh token is invalid or expired.
 *
 * This typically occurs when:
 * - Refresh token has expired
 * - Refresh token has been revoked
 * - Invalid refresh token was provided
 *
 * User re-authorization is required to resolve this error.
 *
 * Class InvalidGrantException
 *
 * @package Bitrix24\SDK\Core\Exceptions
 */
class InvalidGrantException extends BaseException
{
}
