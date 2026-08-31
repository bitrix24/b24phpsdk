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

namespace Bitrix24\SDK\Services\Documentgenerator\Role\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class RoleItemResult
 *
 * @property-read int         $id
 * @property-read string      $name
 * @property-read string      $code
 * @property-read array|null  $permissions
 */
class RoleItemResult extends AbstractItem
{
}
