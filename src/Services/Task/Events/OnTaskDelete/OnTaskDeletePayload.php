<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Events\OnTaskDelete;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read  array{
 *	 	ID: int,
 *	 } $FIELDS_BEFORE
 * @property-read  string $FIELDS_AFTER
 * @property-read  string $IS_ACCESSIBLE_BEFORE
 * @property-read  string $IS_ACCESSIBLE_AFTER
 */
class OnTaskDeletePayload extends AbstractItem
{
}
