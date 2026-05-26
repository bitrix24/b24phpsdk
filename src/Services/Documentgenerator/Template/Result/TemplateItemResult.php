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

namespace Bitrix24\SDK\Services\Documentgenerator\Template\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * Class TemplateItemResult
 *
 * @property-read int $id
 * @property-read string|null $name
 * @property-read string|null $region
 * @property-read string|null $code
 * @property-read string|null $download
 * @property-read string|null $active
 * @property-read string|null $moduleId
 * @property-read int|null $numeratorId
 * @property-read string|null $withStamps
 * @property-read array|null $providers
 * @property-read array|null $users
 * @property-read string|null $isDeleted
 * @property-read string|null $isDefault
 * @property-read int|null $sort
 * @property-read CarbonImmutable|null $createTime
 * @property-read CarbonImmutable|null $updateTime
 * @property-read int|null $createdBy
 * @property-read int|null $updatedBy
 * @property-read int|null $fileId
 * @property-read string|null $bodyType
 * @property-read string|null $productsTableVariant
 * @property-read string|null $downloadMachine
 */
class TemplateItemResult extends AbstractAnnotatedItem
{
}
