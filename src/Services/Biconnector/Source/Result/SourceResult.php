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

namespace Bitrix24\SDK\Services\Biconnector\Source\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SourceResult
 *
 * Wraps the response from biconnector.source.get.
 *
 * The API returns:
 *   result.item.connection.{id, type, code, title, description, active, dateCreate, dateUpdate, createdById, updatedById}
 *   result.item.connectorId
 *   result.item.settings
 *
 * We flatten connection fields to the root level so SourceItemResult has a consistent flat structure.
 */
class SourceResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function source(): SourceItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['item']) && is_array($result['item'])) {
            $item = $result['item'];

            // Flatten nested 'connection' fields to root level
            if (!empty($item['connection']) && is_array($item['connection'])) {
                $connection = $item['connection'];
                unset($item['connection']);
                $item = array_merge($connection, $item);
            }

            return new SourceItemResult($item);
        }

        return new SourceItemResult($result);
    }
}
