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

namespace Bitrix24\SDK\Services\Biconnector\Dataset\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;

/**
 * Class AddedDatasetResult
 *
 * Wraps the response from biconnector.dataset.add.
 * The API returns: result.id (integer)
 */
class AddedDatasetResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['id'])) {
            return (int)$result['id'];
        }

        return (int)$result;
    }
}

