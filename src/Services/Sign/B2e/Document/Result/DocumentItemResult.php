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

namespace Bitrix24\SDK\Services\Sign\B2e\Document\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read string|null $uid Unique identifier of the document
 * @property-read array<string, string>|null $state Document status with 'code' and 'name' keys
 * @property-read array<int, array<string, mixed>>|null $members List of signing members
 */
class DocumentItemResult extends AbstractAnnotatedItem
{
}
