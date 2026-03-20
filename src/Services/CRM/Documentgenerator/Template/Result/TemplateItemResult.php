<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * Class TemplateItemResult
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $region
 * @property-read string|null $code
 * @property-read string|null $download
 * @property-read string|null $moduleId
 * @property-read string|null $active
 * @property-read int|null $numeratorId
 * @property-read int|null $withStamps
 * @property-read int|null $isDeleted
 * @property-read array|null $providers
 * @property-read array|null $users
 * @property-read string|null $sort
 * @property-read string|null $createTime
 * @property-read string|null $updateTime
 * @property-read int|null $createdBy
 * @property-read int|null $updatedBy
 */
class TemplateItemResult extends AbstractCrmItem
{
}

