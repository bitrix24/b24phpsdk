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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class DocumentsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Document\Result
 */
class DocumentsResult extends AbstractResult
{
    /**
     * @return DocumentItemResult[]
     * @throws BaseException
     */
    public function getDocuments(): array
    {
        $items = [];
        $source = [];

        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['documents']) && is_array($result['documents'])) {
            $source = $result['documents'];
        } elseif (!empty($result['items']) && is_array($result['items'])) {
            $source = $result['items'];
        }

        foreach ($source as $item) {
            $items[] = new DocumentItemResult($item);
        }

        return $items;
    }
}
