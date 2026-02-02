<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Lists\Lists\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class ListsResult
 *
 * @package Bitrix24\SDK\Services\Lists\Lists\Result
 */
class ListsResult extends AbstractResult
{
    /**
     * @return ListItemResult[]
     * @throws BaseException
     */
    public function getLists(): array
    {
        $lists = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // Handle both single list and array of lists
        if (isset($result['ID'])) {
            // Single list
            $lists[] = new ListItemResult($result);
        } else {
            // Array of lists
            foreach ($result as $item) {
                $lists[] = new ListItemResult($item);
            }
        }

        return $lists;
    }
}
