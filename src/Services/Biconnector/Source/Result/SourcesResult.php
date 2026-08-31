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
 * Class SourcesResult
 *
 * Wraps the response from biconnector.source.list.
 * The API returns a flat array of source items.
 */
class SourcesResult extends AbstractResult
{
    /**
     * @return SourceItemResult[]
     * @throws BaseException
     */
    public function getSources(): array
    {
        $items = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (array_is_list($result)) {
            foreach ($result as $item) {
                $items[] = new SourceItemResult($item);
            }
        }

        return $items;
    }
}
