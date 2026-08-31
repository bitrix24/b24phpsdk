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
use Carbon\CarbonImmutable;

/**
 * Class DocumentItemResult
 *
 * @property-read int $id
 * @property-read string $title
 * @property-read string $number
 * @property-read int $templateId
 * @property-read int $entityTypeId
 * @property-read int $entityId
 * @property-read CarbonImmutable|null $createTime
 * @property-read CarbonImmutable|null $updateTime
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
    /**
     * @param int|string $offset
     *
     * @return bool|CarbonImmutable|int|mixed|null
     */
    #[\Override]
    public function __get($offset)
    {
        switch ($offset) {
            case 'createTime':
            case 'updateTime':
                if (isset($this->data[$offset]) && $this->data[$offset] !== '') {
                    return CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset]);
                }

                return null;
            case 'templateId':
            case 'entityTypeId':
            case 'entityId':
            case 'numeratorId':
            case 'stampsEnabled':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
            case 'isTransformationError':
                if ($this->data[$offset] !== null) {
                    return (bool)$this->data[$offset];
                }

                return null;
            default:
                return parent::__get($offset);
        }
    }
}
