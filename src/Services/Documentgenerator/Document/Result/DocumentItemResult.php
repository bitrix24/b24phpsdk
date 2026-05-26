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

namespace Bitrix24\SDK\Services\Documentgenerator\Document\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * Class DocumentItemResult
 *
 * @property-read int $id
 * @property-read string $title
 * @property-read string $number
 * @property-read int $templateId
 * @property-read string $provider
 * @property-read string $value
 * @property-read int|null $fileId
 * @property-read int|null $imageId
 * @property-read int|null $pdfId
 * @property-read CarbonImmutable|null $createTime
 * @property-read CarbonImmutable|null $updateTime
 * @property-read array|null $values
 * @property-read int|null $createdBy
 * @property-read int|null $updatedBy
 * @property-read string|null $downloadUrl
 * @property-read string|null $pdfUrl
 * @property-read string|null $imageUrl
 * @property-read bool|null $stampsEnabled
 * @property-read string|null $downloadUrlMachine
 * @property-read string|null $pdfUrlMachine
 * @property-read string|null $imageUrlMachine
 * @property-read string|null $creationMethod
 */
class DocumentItemResult extends AbstractAnnotatedItem
{
    /**
     * @param int|string $offset
     *
     * @return mixed
     */
    #[\Override]
    public function __get($offset)
    {
        if ($offset === 'creationMethod') {
            // The API field name is '_creationMethod' (with leading underscore)
            return $this->data['_creationMethod'] ?? null;
        }

        return parent::__get($offset);
    }
}

