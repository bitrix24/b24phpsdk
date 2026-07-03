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

namespace Bitrix24\SDK\Services\IMBot\File\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * File item returned by imbot.v2.File.upload.
 *
 * @property-read int $id
 * @property-read int $chatId
 * @property-read string $name
 * @property-read string $extension
 * @property-read int $size
 */
class FileItemResult extends AbstractItem
{
}
