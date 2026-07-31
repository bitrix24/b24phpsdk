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

namespace Bitrix24\SDK\Services\Note\Document\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * A single node of the document tree returned by `note.document.tree.list`.
 *
 * `children` is recursive: each element is itself a `DocumentTreeItemResult`.
 *
 * @property-read int                           $id
 * @property-read int                           $collectionId
 * @property-read int|null                      $parentId
 * @property-read string                        $title
 * @property-read int|null                      $position
 * @property-read array<DocumentTreeItemResult>  $children
 */
class DocumentTreeItemResult extends AbstractAnnotatedItem
{
}
