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
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class DatasetResult
 *
 * Wraps the response from biconnector.dataset.get.
 * The API returns: result.item (object)
 */
class DatasetResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function dataset(): DatasetItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['item']) && is_array($result['item'])) {
            return new DatasetItemResult($result['item']);
        }

        return new DatasetItemResult($result);
    }
}

