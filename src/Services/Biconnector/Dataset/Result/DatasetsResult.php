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
 * Class DatasetsResult
 *
 * Wraps the response from biconnector.dataset.list.
 * The API returns a flat array of dataset items.
 */
class DatasetsResult extends AbstractResult
{
    /**
     * @return DatasetItemResult[]
     * @throws BaseException
     */
    public function getDatasets(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (array_is_list($result)) {
            foreach ($result as $item) {
                $items[] = new DatasetItemResult($item);
            }
        }

        return $items;
    }
}

