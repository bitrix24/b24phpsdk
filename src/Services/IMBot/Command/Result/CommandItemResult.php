<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMBot\Command\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Single command item returned by imbot.v2.Command.* methods.
 *
 * @property-read int $id
 * @property-read int $botId
 * @property-read string $command
 * @property-read bool $common
 * @property-read bool $hidden
 * @property-read bool $extranetSupport
 */
class CommandItemResult extends AbstractAnnotatedItem
{
}
