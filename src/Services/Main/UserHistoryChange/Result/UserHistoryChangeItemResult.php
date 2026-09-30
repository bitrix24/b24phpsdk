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

namespace Bitrix24\SDK\Services\Main\UserHistoryChange\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Service\UserHistoryChangeSelectBuilder;

/**
 * @property-read int|null               $id
 * @property-read int|null               $historyId
 * @property-read string|null            $field
 * @property-read mixed                  $data
 */
#[OpenApiEntity('bitrix.main.historyfielddto', selectBuilder: UserHistoryChangeSelectBuilder::class)]
class UserHistoryChangeItemResult extends AbstractAnnotatedItem
{
}
