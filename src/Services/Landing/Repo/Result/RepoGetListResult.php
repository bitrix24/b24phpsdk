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

namespace Bitrix24\SDK\Services\Landing\Repo\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RepoGetListResult extends AbstractResult
{
    /**
     * @return RepoItemResult[]
     * @throws BaseException
     */
    public function getRepoItems(): array
    {
        $res = [];
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        // API возвращает массив элементов репозитория
        if (is_array($result)) {
            foreach ($result as $item) {
                if (is_array($item)) {
                    $res[] = new RepoItemResult($item);
                }
            }
        }

        return $res;
    }
    
    /**
     * Alias for getRepoItems() to match naming convention
     * @return RepoItemResult[]
     * @throws BaseException
     */
    public function getItems(): array
    {
        return $this->getRepoItems();
    }
}