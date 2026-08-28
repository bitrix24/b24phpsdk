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

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserfieldDocumentsResult extends AbstractResult
{
    /**
     * @return UserfieldDocumentItemResult[]
     * @throws BaseException
     */
    public function getDocuments(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): UserfieldDocumentItemResult => new UserfieldDocumentItemResult($item),
            $result['documents'] ?? []
        );
    }

    /**
     * @throws BaseException
     */
    public function getNext(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getNextItem();
    }

    /**
     * @throws BaseException
     */
    public function getTotal(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal();
    }
}
