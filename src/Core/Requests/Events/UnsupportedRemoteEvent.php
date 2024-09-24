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

namespace Bitrix24\SDK\Core\Requests\Events;

use Bitrix24\SDK\Application\Requests\Events\AbstractEventRequest;

/**
 * The UnsupportedRemoteEvent class is a concrete implementation of the AbstractEventRequest class.
 *
 * This class represents an unsupported event request in the system. It is used when an unsupported
 * event is encountered during processing.
 *
 * This class inherits all the properties and methods from the AbstractEventRequest class.
 *
 * @see AbstractEventRequest
 */
class UnsupportedRemoteEvent extends AbstractEventRequest
{
}