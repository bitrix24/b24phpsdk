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

namespace Bitrix24\SDK\Services\HumanResources\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int|null    $userId
 * @property-read string|null $name
 * @property-read string|null $workPosition
 * @property-read string|null $avatar
 * @property-read string|null $url
 * @property-read array|null  $departments
 * @property-read array|null  $teams
 */
class EmployeeItemResult extends AbstractAnnotatedItem
{
}
