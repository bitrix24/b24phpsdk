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
 * A single full-text search hit returned by `note.document.search.list`.
 *
 * @property-read int         $documentId
 * @property-read int         $collectionId
 * @property-read string      $title
 * @property-read float       $score
 * @property-read string|null $snippet
 * @property-read bool        $sharedAccess
 */
class DocumentSearchItemResult extends AbstractAnnotatedItem
{
}
