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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * Class DocumentItemResult
 *
 * @property-read int $id
 * @property-read string $title
 * @property-read string $number
 * @property-read int $templateId
 * @property-read int $entityTypeId
 * @property-read int $entityId
 * @property-read string|null $createTime
 * @property-read string|null $updateTime
 * @property-read int|null $createdBy
 * @property-read int|null $updatedBy
 * @property-read string|null $value
 * @property-read string|null $pdfUrlMachine
 * @property-read string|null $imageUrlMachine
 * @property-read string|null $pdfUrl
 * @property-read string|null $imageUrl
 * @property-read string|null $publicUrl
 * @property-read string|null $downloadUrl
 * @property-read string|null $downloadUrlMachine
 * @property-read array|null $values
 * @property-read array|null $fields
 * @property-read int|null $numeratorId
 * @property-read int|null $stampsEnabled
 * @property-read string|null $fileUrl
 * @property-read bool|null $isTransformationError
 */
class DocumentItemResult extends AbstractCrmItem
{
}
