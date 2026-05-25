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

namespace Bitrix24\SDK\Services\IM\Search\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $full_name
 * @property-read int $manager_user_id
 * @property-read array|null $manager_user_data
 */
class SearchDepartmentItemResult extends AbstractAnnotatedItem
{
}
