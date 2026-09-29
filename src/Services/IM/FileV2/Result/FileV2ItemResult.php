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

namespace Bitrix24\SDK\Services\IM\FileV2\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * File item returned by im.v2.File.upload.
 *
 * @property-read int $id
 * @property-read int $chatId
 * @property-read string $name
 * @property-read string $extension
 * @property-read int $size
 */
class FileV2ItemResult extends AbstractItem
{
}
