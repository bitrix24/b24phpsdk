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

namespace Bitrix24\SDK\Services\Note\File\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * A single document file attachment returned by `note.file.*`.
 *
 * @property-read int         $id
 * @property-read int         $documentId
 * @property-read string      $name
 * @property-read int|null    $size
 * @property-read string|null $mimeType
 * @property-read string|null $assetType
 * @property-read string|null $assetMarkdown
 */
#[OpenApiEntity(entityKey: 'bitrix.note.fileitemdto')]
class FileItemResult extends AbstractAnnotatedItem
{
}
