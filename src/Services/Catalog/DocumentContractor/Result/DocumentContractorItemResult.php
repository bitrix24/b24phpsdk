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

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $id
 * @property-read int $documentId
 * @property-read int $entityTypeId
 * @property-read int $entityId
 */
class DocumentContractorItemResult extends AbstractItem
{
    /**
     * @param int|string $offset
     *
     * @return int|mixed|null
     */
    public function __get($offset)
    {
        switch ($offset) {
            case 'id':
            case 'documentId':
            case 'entityTypeId':
            case 'entityId':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
            default:
                return $this->data[$offset] ?? null;
        }
    }
}
