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

namespace Bitrix24\SDK\Services\Landing\Block\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $code
 * @property-read string $id
 * @property-read string $name
 * @property-read string $namespace
 * @property-read string $new
 * @property-read string $version
 * @property-read array $type
 * @property-read array $section
 * @property-read string $system
 * @property-read string $description
 * @property-read string $preview
 * @property-read string $restricted
 * @property-read string $repo_id
 * @property-read string $app_code
 * @property-read string $only_for_license
 * @property-read string $requires_updates
 */
class RepositoryBlockItemsResult extends AbstractItem
{
}
