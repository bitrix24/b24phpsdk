<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Repo\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $ID
 * @property-read string $XML_ID
 * @property-read string $APP_CODE
 * @property-read string $ACTIVE
 * @property-read string $NAME
 * @property-read string $DESCRIPTION
 * @property-read string $SECTIONS
 * @property-read string $PREVIEW
 * @property-read array $MANIFEST
 * @property-read string $CONTENT
 * @property-read int $CREATED_BY_ID
 * @property-read int $MODIFIED_BY_ID
 * @property-read string $DATE_CREATE
 * @property-read string $DATE_MODIFY
 */
class RepoItemResult extends AbstractItem
{
}