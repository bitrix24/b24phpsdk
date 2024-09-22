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
 * When application_token from application mismatch with application_token in remote event request
 *
 * @see https://apidocs.bitrix24.com/api-reference/events/safe-event-handlers.html
 */
class WrongSecuritySignatureException extends BaseException
{
}