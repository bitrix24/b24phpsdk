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
 * Exception thrown when Bitrix24 portal domain is not found or inaccessible.
 *
 * This typically occurs when:
 * - Portal domain does not exist
 * - Portal has been deleted or suspended
 * - Portal URL is invalid or inaccessible
 *
 * Class PortalDomainNotFoundException
 *
 * @package Bitrix24\SDK\Core\Exceptions
 */
class PortalDomainNotFoundException extends BaseException
{
}
