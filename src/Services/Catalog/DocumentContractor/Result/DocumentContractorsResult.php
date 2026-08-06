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

namespace Bitrix24\SDK\Services\Catalog\DocumentContractor\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DocumentContractorsResult extends AbstractResult
{
    /**
     * @return DocumentContractorItemResult[]
     * @throws BaseException
     */
    public function getDocumentContractors(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): DocumentContractorItemResult => new DocumentContractorItemResult($item),
            $result['documentContractor'] ?? []
        );
    }
}
