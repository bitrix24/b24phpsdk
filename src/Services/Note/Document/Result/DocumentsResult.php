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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DocumentsResult extends AbstractResult
{
    /**
     * @return DocumentItemResult[]
     * @throws BaseException
     */
    public function getDocuments(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new DocumentItemResult($item);
        }

        return $items;
    }

    /**
     * Returns the opaque API cursor without normalizing its fields.
     * Only a null cursor ends the walk: a page may be empty while the cursor is set.
     *
     * @return array<string, mixed>|null
     * @throws BaseException
     */
    public function getNextCursor(): ?array
    {
        return $this->getCoreResponse()->getResponseData()->getResult()['nextCursor'] ?? null;
    }
}
