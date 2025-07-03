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

namespace Bitrix24\SDK\Services\CRM\Timeline\Comment\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * Class CommentItemResult
 *
 * @property-read int $ID
 * @property-read CarbonImmutable $CREATED
 * @property-read int|null $ENTITY_ID
 * @property-read string|null $ENTITY_TYPE
 * @property-read int|null $AUTHOR_ID
 * @property-read string|null $COMMENT
 * @property-read array|null $FILES
 */
class CommentItemResult extends AbstractCrmItem
{
}
