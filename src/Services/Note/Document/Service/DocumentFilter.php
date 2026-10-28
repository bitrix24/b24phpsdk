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

namespace Bitrix24\SDK\Services\Note\Document\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

/**
 * REST v3 filter for `note.document.list`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/note/document/note-document-list.html
 */
class DocumentFilter extends AbstractFilterBuilder
{
    // Identifiers

    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function collectionId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('collectionId', $this);
    }

    public function parentId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('parentId', $this);
    }

    // String fields

    /**
     * Values are {@see \Bitrix24\SDK\Services\Note\Document\Result\DocumentState} cases.
     */
    public function state(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('state', $this);
    }
}
